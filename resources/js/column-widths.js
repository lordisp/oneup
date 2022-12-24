const storageKey = 'columnWidths';

export function saveColumnWidths(columnWidths) {
    localStorage.setItem(storageKey, JSON.stringify(columnWidths));
}

export function loadColumnWidths() {
    return JSON.parse(localStorage.getItem(storageKey)) || {};
}
