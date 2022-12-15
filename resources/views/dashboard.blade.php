<x-app-layout>
    <div class="content">
        Hi {{auth()->user()->firstName }}, you're logged in!
        <p>
            Use the actions from the sidebar. If you miss some menus, contact the cloud team at <a
                    class="hover:underline" x-tooltip.top="cloud.giti@dlh.de"
                    href="mailto:cloud.giti@dlh.de"><i>GI/TI, FRA CLOUD</i></a>
        </p>
        <p class="font-bold">
            Your GITI Cloud Team!
        </p>
        <p class="text-xs italic">
            P.s.: don't forget to subscribe our <a target="_blank" class="hover:underline text-blue-600 dark:text-lhg-yellow after:content-['_↗']" href="https://web.yammer.com/main/groups/eyJfdHlwZSI6Ikdyb3VwIiwiaWQiOiIxMDc2ODcxMTY5In0">Yammer Group</a> to never miss important information!
        </p>
    </div>
</x-app-layout>
