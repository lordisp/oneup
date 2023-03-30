import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Focus from '@alpinejs/focus'

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/animations/scale.css';
import 'tippy.js/themes/light.css';

import '../css/components/choice.css'

import {loadColumnWidths, saveColumnWidths} from './column-widths';

Alpine.plugin(collapse)
Alpine.plugin(Focus)
window.Alpine = Alpine

queueMicrotask(() => {
    Alpine.start()
});

/* Directive: x-tooltip
*
* Example:
*
*   <button type="button" x-tooltip="I'm a tooltip">Hover me</button>
*
* optionally you may want to controle the placement of the tooltip. You can do this with a directive modifier:
*
*   <button type="button" x-tooltip.left="I'm a tooltip">Hover me</button>
*
* */
document.addEventListener('alpine:init', () => {
    Alpine.directive('tooltip', (el, {expression, modifiers}) => {
        tippy(el, {
            content: expression,
            placement: modifiers[0] ?? 'auto',
            theme: JSON.parse(localStorage.getItem('darkMode')) ? 'light-border' : null
        })
        el.classList.add('tooltips')
    })
})

/* Resizeable Table Event-Listener */
window.addEventListener('load', function () {
    const table = document.querySelector('.resizeable');
    let isResizing = false;
    let currentTh;
    let currentWidth;
    let currentX;

    const columnWidths = loadColumnWidths();
    if (table) {
        for (const th of table.querySelectorAll('th')) {
            const columnId = th.dataset.columnId;
            if (columnId in columnWidths) {
                th.style.width = `${columnWidths[columnId]}px`;
            }
        }


    table.addEventListener('mousedown', e => {
        if (e.target.tagName === 'TH') {
            isResizing = true;
            currentTh = e.target;
            currentWidth = currentTh.offsetWidth;
            currentX = e.clientX;
        }
    });

    table.addEventListener('mousemove', e => {
        if (isResizing) {
            const dx = e.clientX - currentX;
            currentTh.style.width = `${currentWidth + dx}px`;
        }
    });

    table.addEventListener('mouseup', e => {
        if (isResizing) {
            const columnId = currentTh.dataset.columnId;
            columnWidths[columnId] = currentTh.offsetWidth;
            saveColumnWidths(columnWidths);
            isResizing = false;
        }
    });
    }
});