<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { UserPlus, Search, Phone, ArrowLeft, Pencil, Download } from 'lucide-vue-next';
import customersRoutes from '@/routes/customers';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    customers: Array<{
        id: number;
        name: string;
        phone: string;
    }>
}>();

const showCreateModal = ref(false);
const isEditing = ref(false);
const selectedCustomerId = ref<number | null>(null);

const form = useForm({
    name: '',
    phone: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedCustomerId.value = null;
    form.reset();
    showCreateModal.value = true;
};

const editCustomer = (customer: { id: number; name: string; phone: string }) => {
    isEditing.value = true;
    selectedCustomerId.value = customer.id;
    form.name = customer.name;
    form.phone = customer.phone || '';
    showCreateModal.value = true;
};

const submit = () => {
    if (isEditing.value && selectedCustomerId.value) {
        form.patch(customersRoutes.update({ customer: selectedCustomerId.value }).url, {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(customersRoutes.store().url, {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Clientes', href: customersRoutes.index().url },
];
</script>

<template>
    <Head title="Directorio de Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1600px] mx-auto pb-24">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Directorio de Clientes</h1>
                    <p class="text-slate-500">Administra la base de datos de tus acreditados</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a 
                        :href="customersRoutes.exportPdf().url"
                        target="_blank"
                        class="inline-flex items-center px-6 py-3 bg-white text-slate-900 border border-slate-200 font-bold rounded-2xl hover:bg-slate-50 transition-all shadow-sm"
                    >
                        <Download :size="18" class="mr-2 text-rose-500" />
                        Exportar PDF
                    </a>

                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-2xl transition-all shadow-lg shadow-teal-100"
                    >
                        <UserPlus :size="20" class="mr-2" />
                        Nuevo Cliente
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                <input 
                    type="text" 
                    placeholder="Buscar por nombre o teléfono..."
                    class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all"
                />
            </div>

            <!-- Customers List -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Nombre Completo</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Teléfono</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right px-12">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="customer in customers" :key="customer.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-400 text-sm">#{{ customer.id }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ customer.name }}</td>
                            <td class="px-6 py-4 text-slate-600 flex items-center">
                                <Phone :size="14" class="mr-2 text-slate-400" />
                                {{ customer.phone || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right px-12 space-x-4">
                                <button 
                                    @click="editCustomer(customer)"
                                    class="text-teal-600 font-bold text-sm hover:text-teal-700 inline-flex items-center gap-1"
                                >
                                    <Pencil :size="14" />
                                    Editar
                                </button>
                                <button class="text-slate-400 font-bold text-sm hover:text-slate-600">Ver Historial</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="customers.length === 0" class="p-12 text-center">
                    <p class="text-slate-400 font-medium">No hay clientes registrados aún.</p>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-8 transform transition-all animate-in fade-in zoom-in duration-300">
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        {{ isEditing ? 'Editar Cliente' : 'Registrar Cliente' }}
                    </h2>
                    <p class="text-slate-500 mb-6 underline decoration-teal-500 underline-offset-4 decoration-2">Información de contacto principal</p>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                placeholder="Ej. Juan Pérez"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Teléfono</label>
                            <input 
                                v-model="form.phone"
                                type="tel" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                placeholder="10 dígitos"
                            />
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button" 
                                @click="showCreateModal = false"
                                class="flex-1 px-4 py-3 text-slate-500 font-bold hover:bg-slate-50 rounded-xl transition-all"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 px-4 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all disabled:opacity-50"
                            >
                                {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Guardar Cliente') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
