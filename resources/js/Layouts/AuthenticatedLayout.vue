<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const showingMobileMenu = ref(false);
</script>

<template>
  <div class="flex h-screen w-full overflow-hidden bg-slate-50">
    <!-- Mobile sidebar backdrop -->
    <div 
      v-if="showingMobileMenu" 
      class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
      @click="showingMobileMenu = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 w-72 transform flex-col border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0 lg:my-4 lg:ml-4 lg:h-[calc(100vh-2rem)] lg:rounded-2xl lg:border lg:shadow-sm"
      :class="[showingMobileMenu ? 'translate-x-0' : '-translate-x-full']"
    >
      <!-- Sidebar Header (Logo) -->
      <div class="flex h-16 shrink-0 items-center border-b border-slate-200 px-6">
        <Link :href="route('dashboard')" class="flex items-center gap-3">
          <ApplicationLogo class="block h-8 w-auto fill-emerald-600 text-emerald-600" />
          <span class="font-serif text-xl font-bold text-slate-900">WalimYuk</span>
        </Link>
      </div>

      <!-- Sidebar Navigation -->
      <div class="flex flex-1 flex-col overflow-y-auto p-4">
        <nav class="space-y-1.5">
          <Link
            :href="route('dashboard')"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="route().current('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <svg class="h-5 w-5 shrink-0" :class="route().current('dashboard') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
          </Link>

          <Link
            href="/weddings"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="route().current('dashboard.weddings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <svg class="h-5 w-5 shrink-0" :class="route().current('dashboard.weddings.*') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Daftar Undangan
          </Link>

          <Link
            v-if="['super_admin', 'admin'].includes($page.props.auth.user.role)"
            href="/users"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="route().current('dashboard.users.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <svg class="h-5 w-5 shrink-0" :class="route().current('dashboard.users.*') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            User Management
          </Link>
        </nav>
      </div>

      <!-- Sidebar Footer (User Profile) -->
      <div class="border-t border-slate-200 p-4">
        <Dropdown align="top" width="56">
          <template #trigger>
            <button class="flex w-full items-center gap-3 rounded-lg p-2 text-left transition-colors hover:bg-slate-100">
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">
                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 truncate">
                <p class="truncate text-sm font-semibold text-slate-900">{{ $page.props.auth.user.name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $page.props.auth.user.email }}</p>
              </div>
              <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </template>

          <template #content>
            <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
            <DropdownLink :href="route('logout')" method="post" as="button">
              Log Out
            </DropdownLink>
          </template>
        </Dropdown>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Mobile header -->
      <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden">
        <Link :href="route('dashboard')" class="flex items-center gap-2">
          <ApplicationLogo class="block h-7 w-auto fill-emerald-600 text-emerald-600" />
          <span class="font-serif text-lg font-bold text-slate-900">WalimYuk</span>
        </Link>
        <button 
          @click="showingMobileMenu = true"
          class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500 focus:outline-none"
        >
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </header>

      <!-- Page Heading (Desktop only if needed, or both) -->
      <header v-if="$slots.header" class="shrink-0 bg-white shadow-sm z-10 hidden lg:block">
        <div class="px-8 py-5">
          <slot name="header" />
        </div>
      </header>
      
      <!-- Mobile Page Heading -->
      <div v-if="$slots.header" class="shrink-0 bg-white shadow-sm z-10 lg:hidden">
        <div class="px-4 py-4">
          <slot name="header" />
        </div>
      </div>

      <!-- Main content scrollable area -->
      <main class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
