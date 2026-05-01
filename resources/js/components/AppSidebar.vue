<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, Users, CreditCard, BarChart3, FileText, Package, UserCheck, PiggyBank, Users2 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import customerRoutes from '@/routes/customers';
import loanRoutes from '@/routes/loans';
import type { NavGroup, NavItem } from '@/types';

const mainNavGroups: NavGroup[] = [
    {
        title: 'Principal',
        items: [
            {
                title: 'Dashboard',
                href: dashboard().url,
                icon: LayoutGrid,
            },
        ],
    },
    {
        title: 'Gestión Operativa',
        items: [
            {
                title: 'Directorio de Clientes',
                href: customerRoutes.index().url,
                icon: Users,
            },
            {
                title: 'Grupos',
                href: '/grupos',
                icon: Users2,
            },
            {
                title: 'Asesores',
                href: '/asesores',
                icon: UserCheck,
            },
        ],
    },
    {
        title: 'Cartera',
        items: [
            {
                title: 'Cartera Individual',
                href: '/cartera-individual',
                icon: Users,
            },
            {
                title: 'Cartera Grupal',
                href: '/cartera-grupal',
                icon: Users2,
            },
        ],
    },
    {
        title: 'Finanzas y Productos',
        items: [
            {
                title: 'Préstamos',
                href: loanRoutes.index().url,
                icon: CreditCard,
            },
            {
                title: 'Ahorro Voluntario',
                href: '/savings',
                icon: PiggyBank,
            },
            {
                title: 'Catálogo',
                href: '/loan-products',
                icon: Package,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
