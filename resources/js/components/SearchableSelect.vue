<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Search, ChevronDown, Check, X } from 'lucide-vue-next';

interface Option {
    id: string | number;
    label: string;
}

const props = defineProps<{
    options: Option[];
    modelValue: string | number | null;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    disabled?: boolean;
}>();

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref<HTMLElement | null>(null);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.id == props.modelValue) || null;
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt => 
        opt.label.toLowerCase().includes(query)
    );
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
    }
};

const selectOption = (option: Option | null) => {
    const value = option ? option.id : '';
    emit('update:modelValue', value);
    emit('change', value);
    isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Trigger -->
        <div 
            @click="toggleDropdown"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between cursor-pointer transition-all hover:bg-white hover:border-slate-300"
            :class="{ 
                'border-slate-900 bg-white ring-4 ring-slate-900/5': isOpen,
                'opacity-50 cursor-not-allowed': disabled 
            }"
        >
            <span v-if="selectedOption" class="text-sm font-bold text-slate-900 truncate">
                {{ selectedOption.label }}
            </span>
            <span v-else class="text-sm font-medium text-slate-400">
                {{ placeholder || 'Seleccionar...' }}
            </span>
            <ChevronDown 
                class="text-slate-400 transition-transform duration-200" 
                :class="{ 'rotate-180': isOpen }"
                :size="18" 
            />
        </div>

        <!-- Dropdown Panel -->
        <div 
            v-if="isOpen"
            class="absolute z-[100] mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200"
        >
            <!-- Search Bar -->
            <div class="p-2 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                <Search class="text-slate-400 ml-2" :size="16" />
                <input 
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder || 'Buscar...'"
                    class="w-full bg-transparent border-none outline-none text-sm font-medium py-2 pr-2 text-slate-900 placeholder:text-slate-400"
                    @click.stop
                    autofocus
                />
            </div>

            <!-- Options List -->
            <div class="max-h-60 overflow-y-auto py-1 custom-scrollbar">
                <div 
                    v-if="placeholder"
                    @click="selectOption(null)"
                    class="px-4 py-2.5 text-xs font-bold text-slate-400 uppercase tracking-widest hover:bg-slate-50 cursor-pointer flex items-center justify-between"
                >
                    {{ placeholder }}
                    <X v-if="!modelValue" :size="14" class="text-slate-400" />
                </div>

                <div 
                    v-for="option in filteredOptions" 
                    :key="option.id"
                    @click="selectOption(option)"
                    class="px-4 py-3 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition-colors group"
                    :class="{ 'bg-slate-50/80': option.id == modelValue }"
                >
                    <span 
                        class="text-sm font-medium transition-colors"
                        :class="option.id == modelValue ? 'text-slate-900 font-bold' : 'text-slate-600 group-hover:text-slate-900'"
                    >
                        {{ option.label }}
                    </span>
                    <Check 
                        v-if="option.id == modelValue" 
                        class="text-slate-900" 
                        :size="16" 
                    />
                </div>

                <div 
                    v-if="filteredOptions.length === 0" 
                    class="px-4 py-8 text-center"
                >
                    <p class="text-xs font-bold text-slate-400 italic">
                        {{ emptyText || 'No se encontraron resultados' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
