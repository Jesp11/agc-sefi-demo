<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    UserPlus, Search, Download, 
    Phone, ChevronRight
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import customersRoutes from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

interface Direccion {
    id_direccion?: number;
    direccion: string;
    entre_calles: string;
    pivot: { tipo: 'casa' | 'trabajo' };
}

interface Customer {
    id_cliente: number;
    nombre: string;
    curp: string;
    clave_elector: string;
    telefono: string;
    ocupacion: string;
    direcciones: Direccion[];
    referencias: any[];
    avales: any[];
}

const props = defineProps<{
    customers: Customer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Directorio de Clientes', href: '#' },
];

const searchQuery = ref('');

const filteredCustomers = computed(() => {
    if (!searchQuery.value) return props.customers;
    const query = searchQuery.value.toLowerCase();
    return props.customers.filter(c => 
        c.nombre.toLowerCase().includes(query) || 
        (c.curp && c.curp.toLowerCase().includes(query)) ||
        (c.telefono && c.telefono.includes(query))
    );
});
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-[1400px] mx-auto p-8 lg:p-12">
            
            <!-- Header Minimalista -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Directorio de Clientes</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Gestión integral de expedientes y acreditados.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a 
                        :href="customersRoutes.exportPdf().url" 
                        target="_blank"
                        class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs"
                    >
                        <Download :size="16" class="mr-2" />
                        Exportar PDF
                    </a>

                    <Link 
                        :href="customersRoutes.create().url"
                        class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                    >
                        <UserPlus :size="16" />
                        Nuevo Registro
                    </Link>
                </div>
            </header>

            <!-- Search Bar Profesional -->
            <div class="relative mb-10">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Búsqueda por nombre, identificador o teléfono..."
                    class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-medium text-slate-900 shadow-sm"
                />
            </div>

            <!-- Tabla de Datos -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[500px]">
                <table class="w-full text-left border-collapse table-fixed">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre del Cliente</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">CURP</th>
                            <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teléfono</th>
                            <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ocupación</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="customer in filteredCustomers" :key="customer.id_cliente" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-slate-400 font-mono">#{{ customer.id_cliente }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900">{{ customer.nombre }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs font-semibold text-slate-600 font-mono">{{ customer.curp || '---' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <Phone :size="12" class="text-slate-300" />
                                    {{ customer.telefono || '---' }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs font-medium text-slate-500 italic">{{ customer.ocupacion || '---' }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end">
                                    <Link 
                                        :href="customersRoutes.show({ customer: customer.id_cliente }).url"
                                        class="px-4 py-2 bg-slate-100 text-slate-900 text-[10px] font-bold uppercase tracking-widest rounded hover:bg-slate-900 hover:text-white transition-all"
                                    >
                                        Ver Detalle
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCustomers.length === 0">
                            <td colspan="6" class="py-20 text-center">
                                <p class="text-slate-400 font-bold italic text-sm tracking-wider">No se encontraron registros que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
