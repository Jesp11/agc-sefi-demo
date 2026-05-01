<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Calculator, Calendar, Landmark, Info, ChevronDown, ArrowLeft, Package, CreditCard, Save, Clock, DollarSign } from 'lucide-vue-next';
import loanRoutes from '@/routes/loans';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    customers: Array<{ id: number; name: string; last_cycle: number; id_asesor: number | null }>;
    asesores: Array<{ id: number; name: string }>;
    products: Array<any>;
}>();

const selectedProductId = ref('');

const applyProductTemplate = () => {
    if (!selectedProductId.value) return;
    const product = props.products.find(p => p.id.toString() === selectedProductId.value);
    if (product) {
        form.monto_otorgado = product.amount.toString();
        form.plazo = product.duration.toString();
    }
};

const form = useForm({
    id_cliente: '',
    monto_otorgado: '',
    interes: 0,
    plazo: '0', // Default changed to 0 as requested
    fecha: new Date().toISOString().split('T')[0],
    id_asesor: '',
    ciclo: '1',
    valor_ficha: '', 
});

// Searchable Customer Dropdown Logic
const customerSearch = ref('');
const showCustomerDropdown = ref(false);

const filteredCustomers = computed(() => {
    if (!customerSearch.value) return props.customers;
    const term = customerSearch.value.toLowerCase();
    return props.customers.filter(c => c.name.toLowerCase().includes(term));
});

const selectCustomer = (customer: { id: number; name: string; last_cycle: number; id_asesor: number | null }) => {
    form.id_cliente = customer.id.toString();
    customerSearch.value = customer.name;
    showCustomerDropdown.value = false;
    
    // Auto-increment cycle based on customer's last cycle
    form.ciclo = (customer.last_cycle + 1).toString();

    // Auto-fill advisor if customer has one assigned
    if (customer.id_asesor) {
        form.id_asesor = customer.id_asesor.toString();
    }
};

// Close dropdown when clicking outside
const closeDropdown = () => { showCustomerDropdown.value = false; };

onMounted(() => {
    document.addEventListener('click', closeDropdown);
    
    // Handle pre-selection from query params
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedId = urlParams.get('id_cliente');
    if (preselectedId) {
        const customer = props.customers.find(c => c.id.toString() === preselectedId);
        if (customer) {
            selectCustomer(customer);
        }
    }
});

onUnmounted(() => document.removeEventListener('click', closeDropdown));

// Auto-calculate Valor Ficha based on the 100/1000 rule
watch(() => form.monto_otorgado, (newVal) => {
    if (newVal) {
        const principal = parseFloat(newVal);
        const autoFicha = (principal / 1000) * 100;
        form.valor_ficha = autoFicha.toString();
    }
});

// Final values calculation
const totals = computed(() => {
    const ficha = parseFloat(form.valor_ficha) || 0;
    const plazo = parseInt(form.plazo) || 0;
    const monto = parseFloat(form.monto_otorgado) || 0;
    
    const total = ficha * plazo;
    const interes = total - monto;
    
    return {
        total,
        interes
    };
});

// Day of week calculation
const dayOfWeek = computed(() => {
    if (!form.fecha) return '';
    const date = new Date(form.fecha + 'T12:00:00'); 
    const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return days[date.getDay()];
});

const submit = () => {
    // Sync calculated interest to form before submission
    form.interes = totals.value.interes;
    
    form.post(loanRoutes.store().url, {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Préstamos', href: loanRoutes.index().url },
    { title: 'Nueva Apertura', href: '#' },
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
        <div class="max-w-6xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex items-center justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="loanRoutes.index().url" 
                        class="text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nueva Operación</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Apertura de Crédito</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link 
                        :href="loanRoutes.index().url"
                        class="text-xs font-bold text-slate-500 hover:text-slate-900 px-4"
                    >
                        Cancelar
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save :size="16" />
                        {{ form.processing ? 'Procesando...' : 'Finalizar Registro' }}
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                <!-- Form -->
                <div class="lg:col-span-2 space-y-12">
                    
                    <!-- Section: Cliente y Asesor -->
                    <section class="space-y-8">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                            <Package :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Configuración Base</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Seleccionar Acreditado</label>
                                <div class="relative" @click.stop>
                                    <input
                                        type="text"
                                        v-model="customerSearch"
                                        @focus="showCustomerDropdown = true"
                                        placeholder="Buscar por nombre..."
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold"
                                    />
                                    <input type="hidden" v-model="form.id_cliente" required>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                                    
                                    <div v-if="showCustomerDropdown" class="absolute z-50 w-full mt-2 bg-white border border-slate-100 shadow-xl rounded-xl max-h-60 overflow-y-auto">
                                        <div 
                                            v-for="customer in filteredCustomers" 
                                            :key="customer.id"
                                            @click="selectCustomer(customer)"
                                            :class="['px-5 py-3 cursor-pointer text-sm font-medium border-b border-slate-50 last:border-0', form.id_cliente == customer.id.toString() ? 'bg-slate-900 text-white' : 'hover:bg-slate-50 text-slate-700']"
                                        >
                                            {{ customer.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Asesor Responsable</label>
                                <div class="relative">
                                    <select v-model="form.id_asesor" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold appearance-none pr-10">
                                        <option value="">Seleccionar Asesor</option>
                                        <option v-for="asesor in asesores" :key="asesor.id" :value="asesor.id">{{ asesor.name }}</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Ciclo del Crédito</label>
                                <input v-model="form.ciclo" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>
                        </div>
                    </section>

                    <!-- Section: Condiciones Financieras -->
                    <section class="space-y-8">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                            <Landmark :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Condiciones del Crédito</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Monto Otorgado</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input v-model="form.monto_otorgado" type="number" step="0.01" class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Valor Ficha (Pago Semanal)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input v-model="form.valor_ficha" type="number" step="0.01" class="w-full pl-8 pr-4 py-3 bg-white border-2 border-slate-900/10 focus:border-slate-900 rounded-lg outline-none transition-all text-sm font-bold text-slate-900" />
                                </div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 pl-1 italic">* Sugerido: 100 por cada 1000</p>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Número de Plazos (Semanas)</label>
                                <input v-model="form.plazo" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Fecha de Desembolso</label>
                                <div class="relative">
                                    <input v-model="form.fecha" type="date" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 pointer-events-none">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 px-2 py-0.5 rounded">{{ dayOfWeek }}</span>
                                    </div>
                                </div>
                            </div>

                            </div>
                    </section>
                </div>

                <!-- Preview Sidebar -->
                <aside class="space-y-8 sticky top-8 self-start">
                    <div class="bg-slate-900 text-white p-8 rounded-2xl shadow-lg">
                        <div class="flex items-center gap-3 mb-8 opacity-60">
                            <Calculator :size="20" />
                            <h3 class="text-[10px] font-bold uppercase tracking-widest">Resumen de Apertura</h3>
                        </div>

                        <div v-if="totals" class="space-y-8">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Monto Total a Liquidar</p>
                                <p class="text-3xl font-bold">{{ formatCurrency(totals.total) }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 pt-6 border-t border-white/10">
                                <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Ficha Semanal</p>
                                    <p class="text-xl font-bold text-emerald-400">{{ formatCurrency(parseFloat(form.valor_ficha) || 0) }}</p>
                                    <p class="text-[9px] font-medium opacity-60 mt-1">Día de pago: {{ dayOfWeek }}s</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Interés Total</p>
                                        <p class="text-sm font-bold">{{ formatCurrency(totals.interes) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Plazos</p>
                                        <p class="text-sm font-bold">{{ form.plazo }} Semanas</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-12 text-center opacity-40 italic space-y-4">
                            <Info :size="32" class="mx-auto mb-2 opacity-20" />
                            <p class="text-xs font-medium">Complete el monto y plazos para calcular la apertura.</p>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-xl border border-slate-100 space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <Clock :size="14" class="text-slate-400" /> Fórmula de Interés
                        </h4>
                        <div class="p-4 bg-white rounded border border-slate-100 space-y-2">
                            <div class="flex justify-between items-center text-[10px]">
                                <span class="text-slate-400 uppercase font-bold">Ficha x Plazos:</span>
                                <span class="font-bold text-slate-900">{{ formatCurrency(totals.total) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[10px]">
                                <span class="text-slate-400 uppercase font-bold">(-) Capital:</span>
                                <span class="font-bold text-slate-900">{{ formatCurrency(parseFloat(form.monto_otorgado) || 0) }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-50 flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-900 uppercase">(=) INTERÉS:</span>
                                <span class="text-xs font-black text-slate-900">{{ formatCurrency(totals.interes) }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
