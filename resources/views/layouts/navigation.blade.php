<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
        Dashboard
    </x-nav-link>

    <x-nav-link :href="route('admin.sectors.index')" :active="request()->routeIs('admin.sectors.*')">
        Secteurs
    </x-nav-link>

    <x-nav-link :href="route('admin.candidatures.index')" :active="request()->routeIs('admin.candidatures.*')">
        Candidatures
    </x-nav-link>

    <x-nav-link :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">
        Paiements
    </x-nav-link>

    <x-nav-link :href="route('admin.providers.index')" :active="request()->routeIs('admin.providers.*')">
        Providers
    </x-nav-link>
</div>