/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme');
module.exports = {
    darkMode: 'class',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        fontFamily: {
            head: ['lufthansa_head', ...defaultTheme.fontFamily.sans],
            'head-bold': ['lufthansa_headhold', ...defaultTheme.fontFamily.sans],
            text: ['lufthansa_text', ...defaultTheme.fontFamily.sans],
        },
        extend: {
            colors: {
                lhg: {
                    text: 'rgb(33,37,41)',
                    blue: '#05164d',
                    yellow: 'rgb(255,174,0)',
                    gray: {
                        6: '#f4f4f4',
                        12: '#e6e6e6',
                        25: '#cccccc',
                        40: '#999999',
                        60: '#787878',
                        80: '#333333',
                    },
                    ui: {
                        GreyBlue: 'rgb(82,98,124)',
                        green: 'rgb(73,166,106)',
                        red: 'rgb(207,50,49)',
                    }
                },
                'dark-nav': '#362222',
                'dark-bg': '#171010',
                'dark-bg1': '#423F3E',
                'dark-bg2': '#2B2B2B',
            },
            height: {
                '60': '3.75rem',
            }
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/line-clamp'),
    ],
    safelist: [
        'animate-pulse',
        'bg-red-400',
        'border-red-900',
        'text-gray-500',
        'bg-amber-50',
        'text-green-500',
        'text-red-500',
        'text-yellow-500',
        'text-red-500',
    ]
}
