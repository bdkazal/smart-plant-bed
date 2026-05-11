import './bootstrap';

function setText(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value ?? 'N/A';
}

function plantBedSoilStatus(value) {
    if (value === null || value === undefined || value === 'N/A') {
        return { label: 'No reading yet', key: 'optimal' };
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return { label: 'No reading yet', key: 'optimal' };
    }

    if (number < 35) {
        return { label: 'Dry', key: 'dry' };
    }

    if (number > 85) {
        return { label: 'Wet', key: 'wet' };
    }

    return { label: 'Optimal', key: 'optimal' };
}

function updatePlantBedSoilGauge(value) {
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
        const status = plantBedSoilStatus(value);
        badge.textContent = status.label;
        badge.className = `soil-state ${status.key}`;
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

        updatePlantBedSoilGauge(soilValue);
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

window.addEventListener('DOMContentLoaded', () => {
    ensurePlantBedSettingsShortcut();
    refreshPlantBedDashboard();
    setInterval(refreshPlantBedDashboard, 5000);
    polishSmartFountainDashboard();
});
