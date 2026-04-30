<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard as dashboardRoute } from '@/routes';
import { Plus, Edit2, Trash2, Tag, Percent, Calendar, Layers, X, Package, ShieldCheck, ShieldAlert } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    products: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Catálogo', href: '/loan-products' },
];

const showModal = ref(false);
const editingProduct = ref<any>(null);

const form = useForm({
    name: '',
    amount: '',
    interest_rate: '',
    frequency: 'weekly',
    duration: '',
    is_active: true
});

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (product: any) => {
    editingProduct.value = product;
    form.name = product.name;
    form.amount = product.amount;
    form.interest_rate = product.interest_rate;
    form.frequency = product.frequency;
    form.duration = product.duration;
    form.is_active = product.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
        editingProduct.value = null;
    }, 200);
};

const saveProduct = () => {
    if (editingProduct.value) {
        form.put(`/loan-products/${editingProduct.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/loan-products', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (product: any) => {
    router.put(`/loan-products/${product.id}`, {
        ...product,
        is_active: !product.is_active
    }, { preserveScroll: true });
};

const deleteProduct = (product: any) => {
    if (confirm(`¿Estás súper seguro de eliminar el producto "${product.name}"? Esto es irreversible.`)) {
        router.delete(`/loan-products/${product.id}`, { preserveScroll: true });
    }
};

const getFrequencyLabel = (freq: string) => {
    const labels: Record<string, string> = {
        daily: 'Diario',
        weekly: 'Semanal',
        fortnightly: 'Quincenal',
        monthly: 'Mensual'
    };
    return labels[freq] || freq;
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val);
};
</script>

<template>
    <Head title="Catálogo de Créditos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-[1400px] mx-auto p-4 md:p-8 pb-24">
            
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        <Package class="text-indigo-600" :size="32" stroke-width="2.5" />
                        Catálogo de Productos
                    </h1>
                    <p class="text-slate-500 font-medium mt-2">Gestiona las plantillas de crédito predefinidas para optimizar la originación.</p>
                </div>
                <div>
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-sm hover:shadow-indigo-500/30 w-full md:w-auto justify-center"
                    >
                        <Plus :size="20" class="mr-2" stroke-width="2.5" />
                        Nuevo Producto
                    </button>
                </div>
            </header>

            <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider">Nombre del Producto</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-right">Monto Base</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-center">Interés</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-center">Plazo</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-center">Estado</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody v-if="products.length > 0">
                            <tr 
                                v-for="product in products" 
                                :key="product.id"
                                class="border-b border-slate-50 transition-colors hover:bg-slate-50"
                                :class="{ 'opacity-60 grayscale': !product.is_active }"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-base">{{ product.name }}</div>
                                    <div class="text-xs text-slate-400 font-medium mt-1">ID: #{{ product.id }}</div>
                                </td>
                                <td class="py-4 px-6 font-bold text-emerald-600 text-right text-lg">
                                    {{ formatCurrency(product.amount) }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-bold bg-amber-50 text-amber-700 border border-amber-200/50">
                                        {{ product.interest_rate }}%
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="font-bold text-slate-700">{{ product.duration }} cuotas</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ getFrequencyLabel(product.frequency) }}s</div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button 
                                        @click="toggleStatus(product)"
                                        :class="[
                                            'inline-flex items-center justify-center p-2 rounded-xl transition-all',
                                            product.is_active ? 'text-green-600 hover:bg-green-50' : 'text-slate-400 hover:bg-slate-100'
                                        ]"
                                        :title="product.is_active ? 'Desactivar Producto' : 'Activar Producto'"
                                    >
                                        <ShieldCheck v-if="product.is_active" :size="20" stroke-width="2.5" />
                                        <ShieldAlert v-else :size="20" stroke-width="2.5" />
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button 
                                        @click="openEditModal(product)"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors focus:outline-none"
                                    >
                                        <Edit2 :size="16" stroke-width="2.5" />
                                    </button>
                                    <button 
                                        @click="deleteProduct(product)"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors focus:outline-none"
                                    >
                                        <Trash2 :size="16" stroke-width="2.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-400 mb-4">
                                        <Package :size="32" stroke-width="1.5" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">No hay productos registrados</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">
                                        Crea tu primer producto de crédito predefinido para estandarizar los préstamos que apruebas.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Creation / Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="closeModal">
            <div class="bg-white rounded-[32px] shadow-2xl max-w-xl w-full p-8 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
                <button @click="closeModal" class="absolute top-6 right-6 text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 p-2 rounded-full transition-colors focus:outline-none">
                    <X :size="20" />
                </button>
                
                <div class="mb-8 pr-12">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                        {{ editingProduct ? 'Editar Producto' : 'Crear Nuevo Producto' }}
                    </h2>
                    <p class="text-slate-500 text-sm mt-1">
                        Establece las reglas exactas para este paquete de crédito.
                    </p>
                </div>

                <form @submit.prevent="saveProduct" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre del Producto</label>
                        <div class="relative">
                            <input 
                                v-model="form.name"
                                type="text"
                                placeholder="Ej: Crédito Nómina Quincenal..."
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-slate-800 font-semibold hover:border-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            />
                            <Tag class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                        </div>
                        <p v-if="form.errors.name" class="mt-2 text-sm text-rose-500 font-bold">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Monto Base</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input 
                                    v-model="form.amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="5000"
                                    class="w-full pl-8 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-slate-800 font-semibold hover:border-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>
                            <p v-if="form.errors.amount" class="mt-2 text-sm text-rose-500 font-bold">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tasa de Interés</label>
                            <div class="relative">
                                <input 
                                    v-model="form.interest_rate"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="15"
                                    class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-slate-800 font-semibold hover:border-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                                <Percent class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                            </div>
                            <p v-if="form.errors.interest_rate" class="mt-2 text-sm text-rose-500 font-bold">{{ form.errors.interest_rate }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Periodicidad</label>
                            <div class="relative">
                                <select 
                                    v-model="form.frequency"
                                    class="w-full pl-11 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-slate-800 font-semibold hover:border-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none cursor-pointer"
                                >
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="fortnightly">Quincenal</option>
                                    <option value="monthly">Mensual</option>
                                </select>
                                <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <p v-if="form.errors.frequency" class="mt-2 text-sm text-rose-500 font-bold">{{ form.errors.frequency }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Plazo (Cuotas)</label>
                            <div class="relative">
                                <input 
                                    v-model="form.duration"
                                    type="number"
                                    min="1"
                                    step="1"
                                    placeholder="12"
                                    class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-slate-800 font-semibold hover:border-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                                <Layers class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                            </div>
                            <p v-if="form.errors.duration" class="mt-2 text-sm text-rose-500 font-bold">{{ form.errors.duration }}</p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button 
                            type="button"
                            @click="closeModal"
                            class="px-6 py-3.5 bg-white text-slate-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3.5 bg-indigo-600 text-white font-bold rounded-xl border border-transparent hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                            {{ editingProduct ? 'Actualizar Producto' : 'Guardar Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
