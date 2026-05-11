function setText(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value ?? 'N/A';
}

function setInputValue(id, value, force = false) {
    const el = document.getElementById(id);
    if (!el || (!force && document.activeElement === el)) return;
    el.value = value ?? '';
}

function setCheckbox(id, checked, force = false) {
    const el = document.getElementById(id);
    if (!el || (!force && document.activeElement === el)) return;
    el.checked = Boolean(checked);
}

function dirtyNoteId(outputKey) {
    return `${outputKey.replace(/_/g, '-')}-dirty-note`;
}

function commandBadgeClass(status) {
    if (status === 'pending') return 'tiny-badge badge-pending';
    if (status === 'acknowledged') return 'tiny-badge badge-info';
    if (status === 'executed') return 'tiny-badge badge-ok';
    if (status === 'failed' || status === 'expired') return 'tiny-badge badge-failed';
    return 'tiny-badge badge-muted';
}

function updateCommandBadge(id, command) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = command?.status_label ?? 'None yet';
    el.className = commandBadgeClass(command?.status);
}

function applyOutputInputs(outputKey, state, force = false) {
    if (outputKey === 'pump') {
        setCheckbox('pump-enabled-input', state.enabled, force);
        setInputValue('pump_speed_percent', state.speed_percent ?? 0, force);
    }

    if (outputKey === 'cob_light') {
        setCheckbox('cob-light-enabled-input', state.enabled, force);
        setInputValue('cob_brightness_percent', state.brightness_percent ?? 0, force);
    }

    if (outputKey === 'rgb_light') {
        setCheckbox('rgb-light-enabled-input', state.enabled, force);
        setInputValue('rgb_brightness_percent', state.brightness_percent ?? 0, force);
        setInputValue('rgb_color', state.color ?? '#FFB066', force);
        setInputValue('rgb_effect', state.effect ?? 'warm_glow', force);
    }
}

function ensureDeviceSettingsShortcut(root) {
    if (document.getElementById('smart-fountain-settings-shortcut')) return;

    const quickActions = root.querySelector('.quick-actions');
    if (!quickActions) return;

    const basePath = window.location.pathname.replace(/\/$/, '');
    const settingsUrl = `${basePath}/settings`;

    const link = document.createElement('a');
    link.id = 'smart-fountain-settings-shortcut';
    link.href = settingsUrl;
    link.className = 'quick-action';
    link.style.gridColumn = '1 / -1';
    link.innerHTML = '<strong>Device Settings</strong><span>Rename this fountain, update area, or timezone</span>';

    quickActions.appendChild(link);
}

function initSmartFountainDashboard() {
    const root = document.querySelector('[data-smart-fountain-dashboard]');
    if (!root) return;

    ensureDeviceSettingsShortcut(root);

    const statusUrl = root.dataset.statusUrl;
    const dirtyForms = new Set();
    const latestOutputStates = {};
    let isWaterSafetyLocked = root.dataset.waterSafetyLocked === '1';
    let isDeviceOnline = true;

    const showDirtyNote = (outputKey, show) => {
        document.getElementById(dirtyNoteId(outputKey))?.classList.toggle('hidden', !show);
    };

    const markFormDirty = (outputKey) => {
        if (outputKey === 'pump' && isWaterSafetyLocked) return;
        if (!isDeviceOnline) return;
        dirtyForms.add(outputKey);
        showDirtyNote(outputKey, true);
    };

    const clearFormDirty = (outputKey) => {
        dirtyForms.delete(outputKey);
        showDirtyNote(outputKey, false);
    };

    const resetFormToCurrent = (outputKey) => {
        const state = latestOutputStates[outputKey] ?? {};
        clearFormDirty(outputKey);
        applyOutputInputs(outputKey, state, true);
    };

    const setOutputFormDisabled = (outputKey, disabled) => {
        const form = document.querySelector(`[data-output-form="${outputKey}"]`);
        if (!form) return;

        form.querySelectorAll('input, select, button[type="submit"]').forEach((control) => {
            control.disabled = disabled;
        });
    };

    const updateOfflineLock = (isOnline) => {
        isDeviceOnline = Boolean(isOnline);

        document.getElementById('offline-note')?.classList.toggle('hidden', isDeviceOnline);

        ['pump', 'cob_light', 'rgb_light'].forEach((outputKey) => {
            if (!isDeviceOnline) clearFormDirty(outputKey);
            setOutputFormDisabled(outputKey, !isDeviceOnline);
        });

        if (!isDeviceOnline) {
            const pumpButton = document.getElementById('pump-submit-button');
            if (pumpButton) pumpButton.textContent = 'Device Offline';
        }
    };

    const updatePumpSafetyLock = (isLocked) => {
        isWaterSafetyLocked = Boolean(isLocked);
        root.dataset.waterSafetyLocked = isWaterSafetyLocked ? '1' : '0';

        const checkbox = document.getElementById('pump-enabled-input');
        const speedInput = document.getElementById('pump_speed_percent');
        const submitButton = document.getElementById('pump-submit-button');
        const safetyNote = document.getElementById('pump-safety-note');
        const badge = document.getElementById('pump-command');

        if (isWaterSafetyLocked) clearFormDirty('pump');

        if (checkbox) {
            checkbox.disabled = !isDeviceOnline || isWaterSafetyLocked;
            if (isWaterSafetyLocked) checkbox.checked = false;
        }

        if (speedInput) {
            speedInput.disabled = !isDeviceOnline || isWaterSafetyLocked;
            if (isWaterSafetyLocked) speedInput.value = 0;
        }

        if (submitButton) {
            submitButton.disabled = !isDeviceOnline || isWaterSafetyLocked;
            submitButton.textContent = !isDeviceOnline
                ? 'Device Offline'
                : (isWaterSafetyLocked ? 'Pump Locked by Water Safety' : 'Send Pump Command');
        }

        safetyNote?.classList.toggle('hidden', !isWaterSafetyLocked);

        if (badge && isWaterSafetyLocked) {
            badge.textContent = 'Locked';
            badge.className = 'tiny-badge badge-warn';
        }
    };

    const updateOnlineStatus = (device) => {
        const online = Boolean(device.is_online);

        setText('device-name', device.name);
        setText('device-status', online ? 'Online' : 'Offline');
        setText('device-type', device.display_type);
        setText('device-location', device.location_label);
        setText('device-timezone', device.timezone);
        setText('device-last-seen', device.last_seen_human);

        const badge = document.getElementById('online-badge');
        if (badge) {
            badge.innerHTML = `<span>${online ? 'Live' : 'Offline'}</span><span class="status-dot"></span>`;
            badge.className = online ? 'status-pill' : 'status-pill offline';
        }

        updateOfflineLock(online);
    };

    const updateWaterSafety = (readings) => {
        const waterLow = readings.water_low;
        const waterLevel = readings.water_level_percent;
        const waterLowEl = document.getElementById('water-low');
        const locked = Number(waterLow) === 1;
        const levelNumber = waterLevel === null || waterLevel === undefined
            ? null
            : Math.max(0, Math.min(100, Number(waterLevel)));

        if (waterLowEl) {
            if (waterLow === null || waterLow === undefined) {
                waterLowEl.textContent = 'N/A';
                waterLowEl.className = 'tiny-badge badge-muted';
            } else if (locked) {
                waterLowEl.textContent = 'Low';
                waterLowEl.className = 'tiny-badge badge-warn';
            } else {
                waterLowEl.textContent = 'Safe';
                waterLowEl.className = 'tiny-badge badge-ok';
            }
        }

        setText('water-level', levelNumber === null ? 'N/A' : `${levelNumber.toFixed(0)}%`);
        setText('water-level-number', levelNumber === null ? 'N/A' : levelNumber.toFixed(0));
        document.getElementById('water-level-percent-sign')?.classList.toggle('hidden', levelNumber === null);
        document.getElementById('water-gauge')?.style.setProperty('--water-level', `${levelNumber ?? 0}%`);
        document.getElementById('water-low-warning')?.classList.toggle('hidden', !locked);
        updatePumpSafetyLock(locked);
    };

    const updateOutputCards = (outputs) => {
        const pump = outputs.pump ?? {};
        const pumpState = pump.state ?? {};
        latestOutputStates.pump = pumpState;
        setText('pump-state', pumpState.enabled ? 'ON' : 'OFF');
        setText('pump-speed', `${pumpState.speed_percent ?? 0}%`);
        setText('pump-source', pump.last_changed_source ?? 'N/A');

        if (!isWaterSafetyLocked) {
            updateCommandBadge('pump-command', pump.last_command);
            if (!dirtyForms.has('pump')) applyOutputInputs('pump', pumpState);
        }

        const cob = outputs.cob_light ?? {};
        const cobState = cob.state ?? {};
        latestOutputStates.cob_light = cobState;
        setText('cob-light-state', cobState.enabled ? 'ON' : 'OFF');
        setText('cob-light-brightness', `${cobState.brightness_percent ?? 0}%`);
        setText('cob-light-source', cob.last_changed_source ?? 'N/A');
        updateCommandBadge('cob-light-command', cob.last_command);
        if (!dirtyForms.has('cob_light')) applyOutputInputs('cob_light', cobState);

        const rgb = outputs.rgb_light ?? {};
        const rgbState = rgb.state ?? {};
        latestOutputStates.rgb_light = rgbState;
        setText('rgb-light-state', rgbState.enabled ? 'ON' : 'OFF');
        setText('rgb-light-brightness', `${rgbState.brightness_percent ?? 0}%`);
        setText('rgb-light-color', rgbState.color ?? 'N/A');
        setText('rgb-light-effect', (rgbState.effect ?? 'N/A').replace(/_/g, ' '));
        setText('rgb-light-source', rgb.last_changed_source ?? 'N/A');
        updateCommandBadge('rgb-light-command', rgb.last_command);
        if (!dirtyForms.has('rgb_light')) applyOutputInputs('rgb_light', rgbState);
    };

    const refreshSmartFountainStatus = async () => {
        if (!statusUrl) return;

        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            const data = await response.json();
            updateOnlineStatus(data.device ?? {});
            updateWaterSafety(data.readings ?? {});
            updateOutputCards(data.outputs ?? {});
        } catch (error) {
            console.error('Smart Fountain status refresh failed:', error);
        }
    };

    document.querySelectorAll('[data-output-form]').forEach((form) => {
        const outputKey = form.dataset.outputForm;

        form.addEventListener('input', (event) => {
            if (event.target.closest('[data-reset-output]')) return;
            markFormDirty(outputKey);
        });

        form.addEventListener('change', (event) => {
            if (event.target.closest('[data-reset-output]')) return;
            markFormDirty(outputKey);
        });

        form.addEventListener('submit', () => clearFormDirty(outputKey));
    });

    document.querySelectorAll('[data-reset-output]').forEach((button) => {
        button.addEventListener('click', () => resetFormToCurrent(button.dataset.resetOutput));
    });

    refreshSmartFountainStatus();
    setInterval(refreshSmartFountainStatus, 5000);

    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess) {
        setTimeout(() => flashSuccess.remove(), 5000);
    }
}

window.addEventListener('DOMContentLoaded', initSmartFountainDashboard);
