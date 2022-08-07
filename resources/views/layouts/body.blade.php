<body x-data="{'darkMode': false}"
      x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => {
            localStorage.setItem('darkMode', JSON.stringify(value))
            Array.from(document.querySelectorAll('.tooltips')).forEach(
              el => el._tippy.setProps({ theme: value ? 'light-border':null }))
        })"
      x-cloak
      :class="{'dark': darkMode === true}"
>
{{ $slot }}
</body>
