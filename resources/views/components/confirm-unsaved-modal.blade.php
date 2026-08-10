@props([
    'show' => 'showLeaveModal',
    'saveAction' => 'saveAndLeave()',
    'discardAction' => 'discardAndLeave()',
    'isSaving' => 'isSaving'
])

<template x-teleport="body">
    <div x-show="{{ $show }}" x-cloak
        class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[#CFE3D5] relative overflow-hidden"
            @click.outside="{{ $show }} = false">

            {{-- Header Icon --}}
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4 border border-amber-200 shadow-xs">
                <i class="fa-solid fa-floppy-disk text-xl"></i>
            </div>

            {{-- Modal Content --}}
            <div class="text-center space-y-2 mb-6">
                <h3 class="text-lg font-extrabold text-slate-900">Perubahan Belum Disimpan</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Anda memiliki data draf pengisian yang belum disimpan secara permanen ke database server.
                </p>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-[11px] font-medium text-left flex items-start gap-2.5 mt-3">
                    <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                    <span class="leading-snug">Apakah Anda ingin menyimpan perubahan tersebut terlebih dahulu sebelum keluar atau berpindah halaman?</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="{{ $saveAction }}" :disabled="{{ $isSaving }}"
                    class="w-full px-4 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <i x-show="{{ $isSaving }}" class="fa-solid fa-spinner animate-spin text-xs"></i>
                    <i x-show="!{{ $isSaving }}" class="fa-solid fa-cloud-arrow-up text-xs"></i>
                    <span>Simpan Perubahan & Lanjutkan</span>
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" @click="{{ $show }} = false; pendingNavigationUrl = null"
                        class="w-1/2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="{{ $discardAction }}"
                        class="w-1/2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs transition-colors">
                        Tinggalkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
