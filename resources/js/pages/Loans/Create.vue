<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Calculator, Calendar, Landmark, Info, ChevronDown, ArrowLeft, Package } from 'lucide-vue-next';
import loanRoutes from '@/routes/loans';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    customers: Array<{ id: number; name: string }>;
    products: Array<any>;
}>();

const selectedProductId = ref('');

const applyProductTemplate = () => {
    if (!selectedProductId.value) {
        return; // maintain existing manual values if they switch back to manual
    }
    const product = props.products.find(p => p.id.toString() === selectedProductId.value);
    if (product) {
        form.amount = product.amount.toString();
        form.interest_rate = product.interest_rate.toString();
        const freqMap: Record<string, string> = {
            'daily': 'diario',
            'weekly': 'semanal',
            'fortnightly': 'quincenal',
            'monthly': 'mensual'
        };
        form.periodicity = freqMap[product.frequency] || 'semanal';
        form.num_installments = product.duration.toString();
    }
};

const form = useForm({
    customer_id: '',
    amount: '',
    interest_rate: '',
    periodicity: 'semanal',
    num_installments: '',
    first_payment_date: new Date().toISOString().split('T')[0],
    promissory_note_folio: '',
});

// Searchable Customer Dropdown Logic
const customerSearch = ref('');
const showCustomerDropdown = ref(false);

const filteredCustomers = computed(() => {
    if (!customerSearch.value) return props.customers;
    const term = customerSearch.value.toLowerCase();
    return props.customers.filter(c => c.name.toLowerCase().includes(term));
});

const selectCustomer = (customer: { id: number; name: string }) => {
    form.customer_id = customer.id.toString();
    customerSearch.value = customer.name;
    showCustomerDropdown.value = false;
};

// Close dropdown when clicking outside
const closeDropdown = () => { showCustomerDropdown.value = false; };
onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));

// Simple preview calculation
const preview = computed(() => {
    if (!form.amount || !form.interest_rate || !form.num_installments) return null;
    
    const principal = parseFloat(form.amount);
    const rate = parseFloat(form.interest_rate);
    const installments = parseInt(form.num_installments);
    
    const interest = (principal * rate) / 100;
    const total = principal + interest;
    const amountPerInstallment = total / installments;
    
    return {
        total,
        interest,
        amountPerInstallment
    };
});

const submit = () => {
    form.post(loanRoutes.store().url, {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Préstamos', href: loanRoutes.index().url },
    { title: 'Nuevo Préstamo', href: loanRoutes.create().url },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};
</script>

<template>
    <Head title="Nuevo Préstamo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto">
            <header class="mb-8">
                <Link 
                    :href="loanRoutes.index().url"
                    class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors mb-4"
                >
                    <ArrowLeft :size="16" class="mr-2" />
                    Volver a Cartera
                </Link>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Crear Nuevo Préstamo</h1>
                <p class="text-slate-500 text-lg">Configura las condiciones del crédito para el cliente</p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submit" class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Template -->
                            <div class="md:col-span-2" v-if="products && products.length > 0">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Producto Preferido (Plantilla)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-indigo-500">
                                        <Package :size="20" stroke-width="2" />
                                    </div>
                                    <select
                                        v-model="selectedProductId"
                                        @change="applyProductTemplate"
                                        class="w-full pl-12 pr-10 py-3 bg-indigo-50/50 border border-indigo-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-indigo-900 font-bold appearance-none cursor-pointer hover:border-indigo-200"
                                    >
                                        <option value="">Personalizado (Captura Manual)</option>
                                        <option v-for="prod in products" :key="prod.id" :value="prod.id.toString()">
                                            {{ prod.name }} ({{ formatCurrency(prod.amount) }} al {{ prod.interest_rate }}%)
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-indigo-400">
                                        <ChevronDown :size="20" stroke-width="2" />
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 pt-2 border-t border-slate-100">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Cliente</label>
                                <div class="relative" @click.stop>
                                    <input
                                        type="text"
                                        v-model="customerSearch"
                                        @focus="showCustomerDropdown = true"
                                        placeholder="Buscar o seleccionar cliente..."
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all placeholder:text-slate-400"
                                    />
                                    <!-- Hidden input to enforce required validation -->
                                    <input type="hidden" v-model="form.customer_id" required>
                                    
                                    <ChevronDown 
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer transition-transform" 
                                        :class="{'rotate-180': showCustomerDropdown}"
                                        @click="showCustomerDropdown = !showCustomerDropdown"
                                        :size="20" 
                                    />
                                    
                                    <!-- Dropdown List -->
                                    <div v-if="showCustomerDropdown" class="absolute z-50 w-full mt-2 bg-white border border-slate-100 shadow-xl rounded-xl max-h-60 overflow-y-auto custom-scrollbar">
                                        <div 
                                            v-for="customer in filteredCustomers" 
                                            :key="customer.id"
                                            @click="selectCustomer(customer)"
                                            :class="['px-5 py-3 cursor-pointer text-sm font-medium transition-colors border-b border-slate-50 last:border-0', form.customer_id == customer.id.toString() ? 'bg-teal-50 text-teal-700' : 'hover:bg-slate-50 text-slate-700']"
                                        >
                                            {{ customer.name }}
                                        </div>
                                        <div v-if="filteredCustomers.length === 0" class="px-5 py-6 text-center text-sm text-slate-400 italic">
                                            No se encontraron clientes coincidentes
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Monto Entregado ($)</label>
                                <input 
                                    v-model="form.amount"
                                    type="number" 
                                    step="0.01"
                                    required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                    placeholder="0.00"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tasa de Interés (%)</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.interest_rate"
                                        type="number" 
                                        step="0.01"
                                        required
                                        class="w-full px-4 py-3 pr-12 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                        placeholder="0.00"
                                    />
                                    <span class="absolute right-8 top-1/2 -translate-y-1/2 font-bold text-slate-400 pointer-events-none">%</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Periodicidad</label>
                                <div class="relative">
                                    <select 
                                        v-model="form.periodicity"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all appearance-none pr-10"
                                    >
                                        <option value="semanal">Semanal</option>
                                        <option value="quincenal">Quincenal</option>
                                        <option value="mensual">Mensual</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="20" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Número de Cuotas</label>
                                <input 
                                    v-model="form.num_installments"
                                    type="number" 
                                    required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                    placeholder="Ej. 12"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Fecha de Primer Pago</label>
                                <input 
                                    v-model="form.first_payment_date"
                                    type="date" 
                                    required
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Folio de Pagaré</label>
                                <input 
                                    v-model="form.promissory_note_folio"
                                    type="text"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                    placeholder="S-001"
                                />
                            </div>
                        </div>

                        <div class="pt-6">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <Landmark :size="20" />
                                {{ form.processing ? 'Procesando...' : 'Crear Préstamo y Generar Tabla' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview Sidebar -->
                <div class="space-y-6">
                    <div class="bg-teal-600 text-white p-8 rounded-3xl shadow-xl shadow-teal-100 sticky top-6">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <Calculator :size="24" />
                            Resumen del Crédito
                        </h3>

                        <div v-if="preview" class="space-y-6">
                            <div class="pb-6 border-b border-teal-500/30">
                                <p class="text-teal-100 text-xs font-bold uppercase tracking-widest mb-1">Monto Total a Pagar</p>
                                <p class="text-4xl font-extrabold">{{ formatCurrency(preview.total) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-teal-100 text-xs font-bold uppercase tracking-widest mb-1">Interés (Global)</p>
                                    <p class="text-lg font-bold">{{ formatCurrency(preview.interest) }}</p>
                                </div>
                                <div>
                                    <p class="text-teal-100 text-xs font-bold uppercase tracking-widest mb-1">Cuotas</p>
                                    <p class="text-lg font-bold">{{ form.num_installments }}</p>
                                </div>
                            </div>

                            <div class="p-4 bg-teal-700/40 rounded-2xl border border-teal-500/20">
                                <p class="text-teal-100 text-xs font-bold uppercase tracking-widest mb-1">Pago por Cuota</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(preview.amountPerInstallment) }}</p>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 text-teal-100/60 italic font-medium">
                            <Info :size="32" class="mx-auto mb-4 opacity-30" />
                            Ingresa el monto, tasa y cuotas para ver la proyección.
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-slate-500 text-sm">
                        <p class="font-bold text-slate-700 mb-2">Nota sobre el cálculo:</p>
                        <p>Esta es una proyección de validación. La tabla de amortización definitiva se generará tras la confirmación.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
