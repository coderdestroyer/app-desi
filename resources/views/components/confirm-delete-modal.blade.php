@props([
    'title' => 'Konfirmasi Hapus Data',
    'warningMessage' => 'Tindakan ini akan menghapus seluruh entri nilai 17 sektor yang terdaftar dan tidak dapat dibatalkan.'
])

<template x-teleport="body">
    <div x-show="isDeleteModalOpen" x-cloak
        class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-100 relative overflow-hidden"
            @click.outside="isDeleteModalOpen = false">

            {{-- Warning Icon Header --}}
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-200 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>

            {{-- Modal Body Content --}}
            <div class="text-center space-y-2 mb-6">
                <h3 class="text-lg font-extrabold text-slate-900">{{ $title }}</h3>
                
                <p class="text-xs text-slate-500 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data 
                    <template x-if="deleteTargetName">
                        <span class="font-extrabold text-slate-900" x-text="deleteTargetName"></span>
                    </template>
                    Tahun <span class="font-extrabold text-slate-900" x-text="deleteTargetYear"></span>?
                </p>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-[11px] font-medium text-left flex items-start gap-2.5 mt-3">
                    <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                    <span class="leading-snug">{{ $warningMessage }}</span>
                </div>
            </div>

            {{-- Action Form --}}
            <form :action="deleteActionUrl" method="POST" class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                @csrf
                @method('DELETE')
                <button type="button" @click="isDeleteModalOpen = false"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors w-full">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition-all w-full flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Ya, Hapus Data</span>
                </button>
            </form>
        </div>
    </div>
</template>
