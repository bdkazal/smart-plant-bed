import './bootstrap';

function setText(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value ?? 'N/A';
}

function plantBedSoilStatus(value, threshold = 35) {
    if (value === null || value === undefined || value === 'N/A') {
        return { label: 'N/A', key: 'unavailable' };
    }

    const number = Number(value);
    const triggerThreshold = Number.isFinite(Number(threshold))
        ? Math.max(0, Math.min(100, Number(threshold)))
        : 35;

    if (!Number.isFinite(number)) {
        return { label: 'N/A', key: 'unavailable' };
    }

    if (number <= 15) {
        return { label: 'Very Dry', key: 'very-dry' };
    }

    if (number <= 30) {
        return { label: 'Dry', key: 'dry' };
    }

    if (number <= triggerThreshold) {
        return { label: 'Low', key: 'low' };
    }

    if (number <= 75) {
        return { label: 'OK', key: 'ok' };
    }

    if (number <= 90) {
        return { label: 'Wet', key: 'wet' };
    }

    return { label: 'Very Wet', key: 'very-wet' };
}

function applyPlantBedSoilBadgeStyle(badge, statusKey) {
    const styles = {
        unavailable: ['#64748b', '#94a3b8', 'rgba(148, 163, 184, 0.16)'],
        'very-dry': ['#991b1b', '#ef4444', 'rgba(239, 68, 68, 0.16)'],
        dry: ['#b45309', '#f59e0b', 'rgba(245, 158, 11, 0.14)'],
        low: ['#a16207', '#eab308', 'rgba(234, 179, 8, 0.16)'],
        ok: ['#15803d', '#22c55e', 'rgba(34, 197, 94, 0.16)'],
        wet: ['#1d4ed8', '#2563eb', 'rgba(37, 99, 235, 0.14)'],
        'very-wet': ['#0e7490', '#06b6d4', 'rgba(6, 182, 212, 0.16)'],
    };

    const [color, dotColor, glowColor] = styles[statusKey] ?? styles.unavailable;

    badge.style.color = color;
    badge.style.setProperty('--soil-dot-color', dotColor);
    badge.style.setProperty('--soil-dot-glow', glowColor);
}

function updatePlantBedSoilGauge(value, threshold = 35) {
    const progress = document.getElementById('soil-gauge-progress');
    const badge = document.getElementById('soil-status-badge');
    const number = Number(value);
    const percent = Number.isFinite(number) ? Math.max(0, Math.min(100, number)) : 0;

    if (progress) {
        const radius = Number(progress.dataset.radius || 70);
        const circumference = 2 * Math.PI * radius;
        progress.style.strokeDasharray = `${circumference}`;
        progress.style.strokeDashoffset = `${circumference - (percent / 100) * circumference}`;
    }

    if (badge) {
        const status = plantBedSoilStatus(value, threshold);
        badge.textContent = status.label;
        badge.className = `soil-state ${status.key}`;
        applyPlantBedSoilBadgeStyle(badge, status.key);
    }
}

function updatePlantBedOnlineBadge(isOnline) {
    const badge = document.getElementById('online-badge');
    if (!badge) return;

    badge.className = isOnline ? 'status-pill' : 'status-pill offline';
    badge.innerHTML = `<span>${isOnline ? 'Live' : 'Offline'}</span><span class="status-dot"></span>`;
}

function plantBedManualLabel(state) {
    if (state === 'waiting') return 'Waiting';
    if (state === 'watering') return 'Watering';
    if (state === 'stopping') return 'Stopping';
    return 'Idle';
}

function updatePlantBedManualState(data) {
    const state = data?.manual?.state ?? 'idle';
    const isOnline = Boolean(data?.device?.is_online);
    const label = plantBedManualLabel(state);

    setText('manual-state-text', label);
    setText('manual-state-badge', label);

    const startWrapper = document.getElementById('start-form-wrapper');
    const stopWrapper = document.getElementById('stop-form-wrapper');
    const offlineNote = document.getElementById('manual-offline-note');
    const startedByRow = document.getElementById('started-by-row');
    const startedByText = document.getElementById('started-by-text');

    if (startedByRow && startedByText) {
        if (['waiting', 'watering', 'stopping'].includes(state) && data?.active_log?.trigger_label) {
            startedByText.textContent = data.active_log.trigger_label;
            startedByRow.classList.remove('hidden');
        } else {
            startedByText.textContent = 'N/A';
            startedByRow.classList.add('hidden');
        }
    }

    offlineNote?.classList.toggle('hidden', isOnline);
    startWrapper?.classList.toggle('hidden', !(state === 'idle' && isOnline));
    stopWrapper?.classList.toggle('hidden', !(['waiting', 'watering'].includes(state) && isOnline));
}

function plantBedStatusUrl() {
    const path = window.location.pathname.replace(/\/$/, '');

    if (!/^\/devices\/\d+$/.test(path)) {
        return null;
    }

    return `${path}/status`;
}

function ensurePlantBedSettingsShortcut() {
    if (!document.getElementById('soil-gauge-progress')) return;

    document.body.classList.add('plant-bed-dashboard');

    if (document.getElementById('plant-bed-settings-shortcut')) return;

    const quickLinks = document.querySelector('.quick-links');
    if (!quickLinks) return;

    const basePath = window.location.pathname.replace(/\/$/, '');
    const settingsUrl = `${basePath}/settings`;

    const link = document.createElement('a');
    link.id = 'plant-bed-settings-shortcut';
    link.href = settingsUrl;
    link.className = 'quick-link';
    link.innerHTML = '<strong>Device Settings</strong><span>Rename this plant bed, update area, or timezone.</span>';

    quickLinks.appendChild(link);
}

async function refreshPlantBedDashboard() {
    const url = plantBedStatusUrl();

    if (!url || !document.getElementById('soil-gauge-progress')) {
        return;
    }

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const isOnline = Boolean(data?.device?.is_online);
        const latestReading = data?.latest_reading ?? {};
        const soilMoistureThreshold = data?.device?.soil_moisture_threshold ?? 35;

        setText('device-name', data?.device?.name);
        setText('device-status', isOnline ? 'Online' : 'Offline');
        setText('device-type', data?.device?.display_type);
        setText('device-location', data?.device?.location_label);
        setText('device-timezone', data?.device?.timezone);
        setText('device-mode', data?.device?.mode_label);
        setText('enabled-schedules', `${data?.device?.enabled_schedule_count ?? 0} enabled`);
        setText('device-last-seen', data?.device?.last_seen_human);

        const soilValue = isOnline ? (latestReading.soil_moisture ?? 'N/A') : 'N/A';
        const temperatureValue = isOnline ? (latestReading.temperature ?? 'N/A') : 'N/A';
        const humidityValue = isOnline ? (latestReading.humidity ?? 'N/A') : 'N/A';

        setText('reading-soil', soilValue);
        setText('reading-temperature', temperatureValue);
        setText('reading-humidity', humidityValue);
        setText('reading-recorded', isOnline ? (latestReading.recorded_at ?? 'N/A') : 'N/A');
        setText('manual-max-duration', data?.manual?.max_duration);

        updatePlantBedSoilGauge(soilValue, soilMoistureThreshold);
        updatePlantBedOnlineBadge(isOnline);
        updatePlantBedManualState(data);
    } catch (error) {
        console.error('Plant Bed dashboard refresh failed:', error);
    }
}

function polishSmartFountainDashboard() {
    if (!document.getElementById('water-gauge')) {
        return;
    }

    document.body.classList.add('smart-fountain-dashboard');

    const metaCards = document.querySelectorAll('.hero-meta .meta-card');
    const locationCard = metaCards[0];
    const lastSeenCard = metaCards[1];
    const appContent = document.querySelector('.app-content');

    if (locationCard) {
        locationCard.classList.add('location-inline-card');
    }

    if (!lastSeenCard || !appContent || document.getElementById('smart-fountain-last-sync')) {
        return;
    }

    const lastSeenValue = lastSeenCard.querySelector('#device-last-seen');
    const initialText = lastSeenValue?.textContent?.trim() || 'Never';

    const syncText = document.createElement('p');
    syncText.id = 'smart-fountain-last-sync';
    syncText.className = 'smart-fountain-last-sync';
    syncText.innerHTML = `Last synced: <span id="device-last-seen">${initialText}</span>`;

    lastSeenCard.remove();
    appContent.appendChild(syncText);
}

function smartFountainScenesStatusUrl() {
    const path = window.location.pathname.replace(/\/$/, '');
    const match = path.match(/^\/devices\/(\d+)\/smart-fountain\/scenes$/);

    if (!match) return null;

    return `/devices/${match[1]}/smart-fountain/status`;
}

function setSmartFountainSceneButtonsOffline(isOffline) {
    document.querySelectorAll('.apply-btn').forEach((button) => {
        button.disabled = isOffline;
        button.textContent = 'Apply Scene';
        button.style.cursor = isOffline ? 'not-allowed' : '';
        button.style.background = isOffline ? '#94a3b8' : '';
        button.style.boxShadow = isOffline ? 'none' : '';
    });
}

function ensureSmartFountainScenesOfflineNotice() {
    if (document.getElementById('scene-offline-note')) return;

    const appContent = document.querySelector('.app-content');
    const hero = appContent?.querySelector('.hero');
    if (!appContent || !hero) return;

    const note = document.createElement('div');
    note.id = 'scene-offline-note';
    note.textContent = 'Device is offline. Scene apply is disabled until the fountain reconnects.';
    note.style.marginBottom = '13px';
    note.style.borderRadius = '18px';
    note.style.border = '1px solid #fde68a';
    note.style.background = '#fffbeb';
    note.style.color = '#92400e';
    note.style.padding = '13px';
    note.style.fontSize = '13px';
    note.style.lineHeight = '1.42';
    note.style.fontWeight = '750';
    hero.insertAdjacentElement('afterend', note);
}

async function refreshSmartFountainScenesPage() {
    const url = smartFountainScenesStatusUrl();
    if (!url || !document.querySelector('.apply-btn')) return;

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const data = await response.json();
        const isOffline = !Boolean(data?.device?.is_online);
        const note = document.getElementById('scene-offline-note');

        if (isOffline) {
            ensureSmartFountainScenesOfflineNotice();
        } else if (note) {
            note.remove();
        }

        setSmartFountainSceneButtonsOffline(isOffline);
    } catch (error) {
        console.error('Smart Fountain scenes status refresh failed:', error);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    ensurePlantBedSettingsShortcut();
    refreshPlantBedDashboard();
    setInterval(refreshPlantBedDashboard, 5000);
    polishSmartFountainDashboard();
    refreshSmartFountainScenesPage();
    setInterval(refreshSmartFountainScenesPage, 5000);
});
