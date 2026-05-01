<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, Phone, User, MapPin, Users, ShieldCheck, 
    Briefcase, Save, Plus, X, FileText, ChevronDown
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import customersRoutes from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

import { onMounted, watch } from 'vue';

const props = defineProps<{
    asesores: Array<{ id_asesor: number, nombre: string }>;
    grupos: Array<{ id_grupo: number, nombre: string, id_asesor: number }>;
}>();

const form = useForm({
    nombre: '',
    id_asesor: '' as number | string,
    id_grupo: '' as number | string,
    curp: '',
    clave_elector: '',
    telefono: '',
    ocupacion: '',
    direcciones: [
        { direccion: '', entre_calles: '', pivot: { tipo: 'casa' } },
        { direccion: '', entre_calles: '', pivot: { tipo: 'trabajo' } }
    ],
    referencias: [] as any[],
    avales: [] as any[]
});

const prefillAsesor = (grupoId: number | string) => {
    const grupo = props.grupos.find(g => g.id_grupo == grupoId);
    if (grupo && grupo.id_asesor) {
        form.id_asesor = grupo.id_asesor;
    }
};

watch(() => form.id_grupo, (newVal) => {
    if (newVal) prefillAsesor(newVal);
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const grupoId = params.get('id_grupo');
    if (grupoId) {
        form.id_grupo = parseInt(grupoId);
        prefillAsesor(form.id_grupo);
    }
});

const addReferencia = () => {
    form.referencias.push({
        nombre: '',
        tipo: 'familiar',
        parentesco: '',
        telefono: '',
        direccion: '',
        anios_relacion: null
    });
};

const addAval = () => {
    form.avales.push({
        nombre: '',
        telefono: '',
        direccion: '',
        parentesco: ''
    });
};

const submit = () => {
    form.post('/customers');
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Clientes', href: customersRoutes.index().url },
    { title: 'Nuevo Registro', href: '#' },
];
</script>

<template>
    <Head title="Nuevo Cliente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="submit" class="max-w-5xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex items-center justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="customersRoutes.index().url" 
                        class="text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nuevo Expediente</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Registro de Acreditado</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link 
                        :href="customersRoutes.index().url"
                        class="text-xs font-bold text-slate-500 hover:text-slate-900 px-4"
                    >
                        Cancelar
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save :size="16" />
                        {{ form.processing ? 'Registrando...' : 'Finalizar Registro' }}
                    </button>
                </div>
            </header>

            <div class="space-y-20">
                
                <!-- Section 1: Identidad -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <FileText :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Datos de Identidad</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre Completo</label>
                                <input v-model="form.nombre" type="text" placeholder="Ej. Juan Pérez López" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" :class="{ 'border-rose-500': form.errors.nombre }" />
                                <p v-if="form.errors.nombre" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.nombre }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Asesor Permanente</label>
                                <SearchableSelect 
                                    v-model="form.id_asesor"
                                    :options="asesores.map(a => ({ id: a.id_asesor, label: a.nombre }))"
                                    placeholder="Seleccionar Asesor"
                                    search-placeholder="Buscar asesor..."
                                    empty-text="No se encontraron asesores"
                                />
                                <p v-if="form.errors.id_asesor" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.id_asesor }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                            <input v-model="form.telefono" type="tel" placeholder="10 dígitos" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" :class="{ 'border-rose-500': form.errors.telefono }" />
                            <p v-if="form.errors.telefono" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.telefono }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Ocupación Actual</label>
                            <input v-model="form.ocupacion" type="text" placeholder="Ej. Comerciante" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" :class="{ 'border-rose-500': form.errors.ocupacion }" />
                            <p v-if="form.errors.ocupacion" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.ocupacion }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">CURP</label>
                            <input v-model="form.curp" type="text" placeholder="18 caracteres" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold font-mono" :class="{ 'border-rose-500': form.errors.curp }" />
                            <p v-if="form.errors.curp" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.curp }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Clave Elector</label>
                            <input v-model="form.clave_elector" type="text" placeholder="ID Credencial" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold font-mono" :class="{ 'border-rose-500': form.errors.clave_elector }" />
                            <p v-if="form.errors.clave_elector" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.clave_elector }}</p>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center gap-2 mb-2">
                                <Users :size="14" class="text-slate-400" />
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Asignación de Grupo (Opcional)</label>
                            </div>
                            
                            <SearchableSelect 
                                v-model="form.id_grupo"
                                :options="grupos.map(g => ({ id: g.id_grupo, label: g.nombre }))"
                                placeholder="Ninguno (Cartera Individual)"
                                search-placeholder="Buscar grupo..."
                                empty-text="No se encontraron grupos"
                            />
                            
                            <p v-if="form.errors.id_grupo" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.id_grupo }}</p>
                            <p class="text-[10px] text-slate-400 italic mt-1 ml-1">Si selecciona un grupo, el cliente pertenecerá a la cartera grupal.</p>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Ubicaciones -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <MapPin :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ubicaciones</h2>
                    </div>
                    <div class="space-y-12">
                        <div v-for="(dir, idx) in form.direcciones" :key="idx" class="pl-6 border-l-2 border-slate-200 space-y-6">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Dirección de {{ dir.pivot.tipo }}</p>
                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Calle, Número y Colonia</label>
                                    <textarea v-model="dir.direccion" rows="2" placeholder="Dirección completa..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold"></textarea>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Referencias Visuales</label>
                                    <input v-model="dir.entre_calles" type="text" placeholder="Ej. Entre calle X y calle Y" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Referencias -->
                <section>
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <Users :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Referencias Personales</h2>
                        </div>
                        <button type="button" @click="addReferencia" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-1 transition-colors">
                            <Plus :size="14" /> Añadir Nueva
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-10">
                        <div v-for="(ref, idx) in form.referencias" :key="idx" class="p-6 bg-slate-50 rounded-lg relative border border-slate-100">
                            <button type="button" @click="form.referencias.splice(idx, 1)" class="absolute top-4 right-4 text-slate-300 hover:text-rose-600 transition-colors">
                                <X :size="16" />
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre</label>
                                    <input v-model="ref.nombre" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Tipo</label>
                                    <div class="relative">
                                        <select v-model="ref.tipo" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold appearance-none pr-10 focus:border-slate-900 transition-all">
                                            <option value="familiar">Familiar</option>
                                            <option value="amistad">Amistad</option>
                                        </select>
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Parentesco</label>
                                    <input v-model="ref.parentesco" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                                    <input v-model="ref.telefono" type="tel" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                            </div>
                        </div>
                        <div v-if="form.referencias.length === 0" class="py-12 text-center text-slate-300 text-xs font-bold italic">No se han añadido referencias.</div>
                    </div>
                </section>

                <!-- Section 4: Avales -->
                <section>
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <ShieldCheck :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Avales y Garantías</h2>
                        </div>
                        <button type="button" @click="addAval" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-1 transition-colors">
                            <Plus :size="14" /> Añadir Nuevo
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-10">
                        <div v-for="(aval, idx) in form.avales" :key="idx" class="p-6 bg-slate-50 rounded-lg relative border border-slate-100">
                            <button type="button" @click="form.avales.splice(idx, 1)" class="absolute top-4 right-4 text-slate-300 hover:text-rose-600 transition-colors">
                                <X :size="16" />
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre</label>
                                    <input v-model="aval.nombre" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Parentesco</label>
                                    <input v-model="aval.parentesco" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                                    <input v-model="aval.telefono" type="tel" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Dirección de Garantía</label>
                                    <textarea v-model="aval.direccion" rows="2" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold"></textarea>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.avales.length === 0" class="py-12 text-center text-slate-300 text-xs font-bold italic">No se han añadido avales.</div>
                    </div>
                </section>
            </div>
        </form>
    </AppLayout>
</template>
