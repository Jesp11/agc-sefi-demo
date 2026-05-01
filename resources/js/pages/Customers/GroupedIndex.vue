<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    UserPlus, Search, Download, 
    Phone, ChevronRight, Users2, ChevronDown
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import customersRoutes from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

interface Direccion {
    id_direccion?: number;
    direccion: string;
    entre_calles: string;
    pivot: { tipo: 'casa' | 'trabajo' };
}

interface Grupo {
    id_grupo: number;
    nombre: string;
}

interface Customer {
    id_cliente: number;
    nombre: string;
    curp: string;
    clave_elector: string;
    telefono: string;
    ocupacion: string;
    id_grupo: number | null;
    grupo: Grupo | null;
    direcciones: Direccion[];
    referencias: any[];
    avales: any[];
}

const props = defineProps<{
    customers: Customer[];
    grupos: Grupo[];
    filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Cartera Grupal', href: '#' },
];

const searchQuery = ref('');
const selectedGroup = ref(props.filters?.grupo_id || '');

const filteredCustomers = computed(() => {
    if (!searchQuery.value) return props.customers;
    const query = searchQuery.value.toLowerCase();
    return props.customers.filter(c => 
        c.nombre.toLowerCase().includes(query) || 
        (c.curp && c.curp.toLowerCase().includes(query)) ||
        (c.telefono && c.telefono.includes(query))
    );
});

watch(selectedGroup, (newValue) => {
    router.get('/cartera-grupal', { grupo_id: newValue }, {
        preserveState: true,
        replace: true
    });
});
</script>

<template>
    <Head title="Cartera Grupal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <header class="flex items-center justify-between gap-8 mb-12">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Cartera Grupal</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Gestión de clientes que pertenecen a un grupo.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a 
                        href="/customers/export-grupal"
                        target="_blank"
                        class="px-6 py-2.5 bg-white border border-slate-200 text-slate-900 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                    >
                        <Download :size="16" />
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

            <div class="flex gap-4 mb-10">
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Búsqueda por nombre, identificador o teléfono..."
                        class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-medium text-slate-900 shadow-sm"
                    />
                </div>
                
                <div class="w-72">
                    <SearchableSelect 
                        v-model="selectedGroup"
                        :options="grupos.map(g => ({ id: g.id_grupo, label: g.nombre }))"
                        placeholder="Todos los Grupos"
                        search-placeholder="Filtrar grupos..."
                        empty-text="No se encontraron grupos"
                    />
                </div>
            </div>

            <!-- Tabla de Datos -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-x-auto min-h-[500px]">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre del Cliente</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grupo</th>
                            <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teléfono</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="customer in filteredCustomers" :key="customer.id_cliente" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-slate-300 font-mono">#{{ customer.id_cliente }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 uppercase tracking-tight">{{ customer.nombre }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono font-bold tracking-tight mt-0.5">{{ customer.curp || 'SIN CURP REGISTRADA' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wider">
                                    {{ customer.grupo?.nombre || '---' }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <Phone :size="12" class="text-slate-300" />
                                    {{ customer.telefono || '---' }}
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end">
                                    <Link 
                                        :href="customersRoutes.show({ customer: customer.id_cliente }).url"
                                        class="px-4 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                    >
                                        Ver Detalle
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCustomers.length === 0">
                            <td colspan="5" class="py-32 text-center">
                                <div class="max-w-xs mx-auto text-slate-400">
                                    <Search :size="48" class="mx-auto mb-4 opacity-10" />
                                    <p class="text-sm font-bold tracking-wider text-slate-900">Sin resultados</p>
                                    <p class="text-xs mt-2 text-slate-500 font-medium leading-relaxed">No se encontraron clientes que coincidan con los filtros aplicados.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
