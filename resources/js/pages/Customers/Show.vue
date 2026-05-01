<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArrowLeft, Phone, User, MapPin, Users, ShieldCheck, 
    Calendar, Landmark, Mail, IdCard, Briefcase, ExternalLink,
    History, TrendingUp, DollarSign, Fingerprint, FileText
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import customersRoutes from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

interface Direccion {
    id_direccion: number;
    direccion: string;
    entre_calles: string;
    pivot: { tipo: 'casa' | 'trabajo' };
}

interface Referencia {
    id_referencia: number;
    nombre: string;
    tipo: 'familiar' | 'amistad';
    parentesco: string;
    telefono: string;
    direccion: string;
    anios_relacion: number;
}

interface Aval {
    id_aval: number;
    nombre: string;
    telefono: string;
    direccion: string;
    parentesco: string;
}

interface Credito {
    id_credito: number;
    fecha: string;
    monto_otorgado: number;
    total: number;
    ciclo: number;
    asesor?: { nombre: string };
}

const props = defineProps<{
    customer: {
        id_cliente: number;
        nombre: string;
        curp: string;
        clave_elector: string;
        telefono: string;
        ocupacion: string;
        direcciones: Direccion[];
        referencias: Referencia[];
        avales: Aval[];
        creditos: Credito[];
    }
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Clientes', href: customersRoutes.index().url },
    { title: props.customer.nombre, href: '#' },
];
</script>

<template>
    <Head :title="`Expediente - ${customer.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-start gap-6">
                    <Link 
                        :href="customersRoutes.index().url" 
                        class="mt-2 text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Expediente de Cliente #{{ customer.id_cliente }}</p>
                        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">{{ customer.nombre }}</h1>
                        <p class="text-slate-500 font-medium mt-1">{{ customer.ocupacion || 'Sin ocupación registrada' }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <Link 
                        :href="customersRoutes.edit({ customer: customer.id_cliente }).url"
                        class="px-6 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                    >
                        Editar Expediente
                    </Link>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-16">
                <!-- Section 1: Información Base -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <FileText :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Información General</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-y-10 gap-x-12">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Identificación CURP</p>
                            <p class="text-sm font-semibold text-slate-900 font-mono">{{ customer.curp || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Clave de Elector</p>
                            <p class="text-sm font-semibold text-slate-900 font-mono">{{ customer.clave_elector || '---' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Teléfono de Contacto</p>
                            <p class="text-sm font-bold text-slate-900">{{ customer.telefono || 'Sin registro' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Ubicaciones -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <MapPin :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ubicaciones Registradas</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div v-for="dir in customer.direcciones" :key="dir.id_direccion">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Domicilio de {{ dir.pivot.tipo }}</p>
                            <p class="text-lg font-semibold text-slate-900 leading-snug mb-3">{{ dir.direccion }}</p>
                            <div class="bg-slate-50 p-3 rounded border border-slate-100">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Referencias Visuales</p>
                                <p class="text-xs text-slate-600 italic font-medium">{{ dir.entre_calles || 'No se proporcionaron referencias adicionales.' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Referencias y Avales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                    <section>
                        <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                            <Users :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Referencias Personales</h2>
                        </div>
                        <div class="space-y-8">
                            <div v-for="ref in customer.referencias" :key="ref.id_referencia" class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">{{ ref.nombre }}</h4>
                                    <p class="text-xs text-slate-500 font-medium">{{ ref.parentesco }} • {{ ref.anios_relacion }} años de relación</p>
                                    <p class="text-xs font-bold text-slate-700 mt-1">{{ ref.telefono || '---' }}</p>
                                </div>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 bg-slate-50 px-2 py-0.5 rounded">{{ ref.tipo }}</span>
                            </div>
                        </div>
                    </section>

                    <section>
                        <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                            <ShieldCheck :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Avales y Garantías</h2>
                        </div>
                        <div class="space-y-8">
                            <div v-for="aval in customer.avales" :key="aval.id_aval">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="font-bold text-slate-900 text-sm">{{ aval.nombre }}</h4>
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">{{ aval.parentesco }}</span>
                                </div>
                                <p class="text-xs font-bold text-slate-700 mb-2">{{ aval.telefono || '---' }}</p>
                                <p class="text-xs text-slate-500 italic">{{ aval.direccion || 'Domicilio no registrado' }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Section 4: Historial de Créditos -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <History :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Historial de Operaciones</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ciclo</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Fecha</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Asesor</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Monto</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Total c/Int</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="credito in customer.creditos" :key="credito.id_credito" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-6 text-sm font-bold text-slate-900">Ciclo {{ credito.ciclo }}</td>
                                    <td class="px-4 py-6 text-sm text-slate-500">{{ credito.fecha }}</td>
                                    <td class="px-4 py-6 text-sm text-slate-900">{{ credito.asesor?.nombre || '---' }}</td>
                                    <td class="px-4 py-6 text-right text-sm text-slate-600">{{ formatCurrency(credito.monto_otorgado) }}</td>
                                    <td class="px-4 py-6 text-right text-base font-bold text-slate-900">{{ formatCurrency(credito.total) }}</td>
                                    <td class="px-4 py-6 text-center">
                                        <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-1 bg-slate-100 text-slate-600 rounded">Activo</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="customer.creditos.length === 0" class="py-12 text-center text-slate-400 text-xs font-medium italic">
                            No existen registros de operaciones previas para este cliente.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.overflow-x-auto {
    scrollbar-width: thin;
}
</style>
