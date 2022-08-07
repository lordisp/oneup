import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Focus from '@alpinejs/focus'

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/animations/scale.css';
import 'tippy.js/themes/light.css';

Alpine.plugin(collapse)
Alpine.plugin(Focus)
window.Alpine = Alpine

queueMicrotask(() => {
    Alpine.start()
});

document.addEventListener('alpine:init', () => {
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
    Alpine.directive('tooltip', (el, {expression, modifiers}) => {
        tippy(el, {
            content: expression,
            placement: modifiers[0] ?? 'auto',
            theme: JSON.parse(localStorage.getItem('darkMode')) ? 'light-border' : null
        })
        el.classList.add('tooltips')
    })
})