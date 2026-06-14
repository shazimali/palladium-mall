@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6">

        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])
        @include('tenants.wizard._tenant_banner')

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 2 — Guarantor / Property Advisor</h1>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Add one or more guarantors. At least one is mandatory.</p>
                </div>
                @if($guarantors->isNotEmpty())
                    <a href="{{ route('tenants.printStep', [$tenant, 2]) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                @endif
            </div>

            <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 2]) }}"
                  enctype="multipart/form-data"
                  class="px-6 py-6 space-y-6"
                  x-data="guarantorManager({{ json_encode(
                      old('guarantors',
                          $guarantors->count() > 0
                          ? $guarantors->map(fn($g) => [
                              'name'              => $g->name,
                              'cnic'              => $g->cnic,
                              'relation'          => $g->relation,
                              'phone'             => $g->phone,
                              'address'           => $g->address,
                              'occupation'        => $g->occupation ?? '',
                              'shop_name'         => $g->shop_name ?? '',
                              'cnic_front_url'    => $g->cnic_front_url,
                              'cnic_back_url'     => $g->cnic_back_url,
                              'photo_url'         => $g->photo_url,
                              'visiting_card_url' => $g->visiting_card_url,
                          ])->toArray()
                          : [['name'=>'','cnic'=>'','relation'=>'','phone'=>'','address'=>'','occupation'=>'','shop_name'=>'','cnic_front_url'=>'','cnic_back_url'=>'','photo_url'=>'','visiting_card_url'=>'']]
                      )
                  ) }}, {{ json_encode($errors->toArray()) }})">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $select = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                    $error = 'mt-1 text-xs text-red-500';
                @endphp

                @error('guarantors') <p class="mb-3 text-xs text-red-500">{{ $message }}</p> @enderror

                {{-- ── Guarantors list ──────────────────────────────────────── --}}
                <template x-for="(g, index) in guarantors" :key="index">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">

                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300"
                                x-text="'Guarantor / Property Advisor ' + (index + 1)"></h4>
                            <button type="button" x-show="guarantors.length > 1" @click="removeGuarantor(index)"
                                class="text-xs text-red-400 hover:text-red-500 font-medium">Remove</button>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            <div>
                                <label class="{{ $label }}">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" :name="'guarantors[' + index + '][name]'" x-model="g.name"
                                    placeholder="Full name" class="{{ $input }}"
                                    :class="getError(index, 'name') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'name')" x-text="getError(index, 'name')" class="{{ $error }}"></p>
                            </div>

                            <div>
                                <label class="{{ $label }}">CNIC <span class="text-red-500">*</span></label>
                                <input type="text" :name="'guarantors[' + index + '][cnic]'" x-model="g.cnic"
                                    @input="g.cnic = formatGuarantorCnic($event.target.value)"
                                    placeholder="35201-1234567-1" maxlength="15" class="{{ $input }}"
                                    :class="getError(index, 'cnic') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'cnic')" x-text="getError(index, 'cnic')" class="{{ $error }}"></p>
                            </div>

                            <div>
                                <label class="{{ $label }}">Relation <span class="text-red-500">*</span></label>
                                <select :name="'guarantors[' + index + '][relation]'" x-model="g.relation" class="{{ $select }}"
                                    :class="getError(index, 'relation') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                    <option value="">Select relation</option>
                                    <option value="dealer">Dealer</option>
                                    <option value="friend">Friend</option>
                                    <option value="relative">Relative</option>
                                    <option value="employer">Employer</option>
                                    <option value="other">Other</option>
                                </select>
                                <p x-show="getError(index, 'relation')" x-text="getError(index, 'relation')" class="{{ $error }}"></p>
                            </div>

                            <div>
                                <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
                                <input type="text" :name="'guarantors[' + index + '][phone]'" x-model="g.phone"
                                    placeholder="03001234567" class="{{ $input }}"
                                    :class="getError(index, 'phone') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'phone')" x-text="getError(index, 'phone')" class="{{ $error }}"></p>
                            </div>

                            <div>
                                <label class="{{ $label }}">Occupation</label>
                                <input type="text" :name="'guarantors[' + index + '][occupation]'" x-model="g.occupation"
                                    placeholder="e.g. Businessman" class="{{ $input }}"
                                    :class="getError(index, 'occupation') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'occupation')" x-text="getError(index, 'occupation')" class="{{ $error }}"></p>
                            </div>

                            <div>
                                <label class="{{ $label }}">Shop Name</label>
                                <input type="text" :name="'guarantors[' + index + '][shop_name]'" x-model="g.shop_name"
                                    placeholder="e.g. Al-Hamza Traders" class="{{ $input }}"
                                    :class="getError(index, 'shop_name') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'shop_name')" x-text="getError(index, 'shop_name')" class="{{ $error }}"></p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="{{ $label }}">Address <span class="text-red-500">*</span></label>
                                <input type="text" :name="'guarantors[' + index + '][address]'" x-model="g.address"
                                    placeholder="Full address" class="{{ $input }}"
                                    :class="getError(index, 'address') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : ''">
                                <p x-show="getError(index, 'address')" x-text="getError(index, 'address')" class="{{ $error }}"></p>
                            </div>

                            {{-- Guarantor File Uploads Grid --}}
                            <div class="sm:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-4 pt-4 border-t border-gray-100 dark:border-gray-800 mt-4">
                                {{-- Photo --}}
                                <div class="space-y-2">
                                    <label class="{{ $label }} text-xs">Photo</label>
                                    
                                    {{-- Preview Card --}}
                                    <div x-show="g.photo_url" class="relative group w-24 h-24 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                        <img :src="g.photo_url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-1.5">
                                            <button type="button" @click="startCamera('guarantors[' + index + '][photo]', 'face')" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="Retake Photo">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="clearGuarantorFile(index, 'photo', 'photo_url')" class="p-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Control Buttons --}}
                                    <div x-show="!g.photo_url" class="flex flex-col gap-1.5">
                                        <button type="button" @click="startCamera('guarantors[' + index + '][photo]', 'face')" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                            Take Photo
                                        </button>
                                        <button type="button" @click="document.getElementById('file_guarantor_' + index + '_photo').click()" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                            Upload
                                        </button>
                                    </div>
                                    
                                    <input type="file" :name="'guarantors[' + index + '][photo]'" :id="'file_guarantor_' + index + '_photo'" accept="image/*" @change="updateGuarantorPreview($event, index, 'photo_url')" class="hidden">
                                    <input type="hidden" :name="'guarantors[' + index + '][delete_photo]'" :value="g.delete_photo ? '1' : '0'">
                                    <p x-show="getError(index, 'photo')" x-text="getError(index, 'photo')" class="{{ $error }} text-[10px]"></p>
                                </div>

                                {{-- CNIC Front --}}
                                <div class="space-y-2">
                                    <label class="{{ $label }} text-xs">CNIC Front</label>
                                    
                                    {{-- Preview Card --}}
                                    <div x-show="g.cnic_front_url" class="relative group w-full h-24 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm"
                                         :class="getError(index, 'cnic_front') ? 'border-red-400' : ''">
                                        <img :src="g.cnic_front_url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-1.5">
                                            <a :href="g.cnic_front_url" target="_blank" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="View Large">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="startCamera('guarantors[' + index + '][cnic_front]', 'card')" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="Retake Photo">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="clearGuarantorFile(index, 'cnic_front', 'cnic_front_url')" class="p-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Control Buttons --}}
                                    <div x-show="!g.cnic_front_url" class="flex flex-col gap-1.5">
                                        <button type="button" @click="startCamera('guarantors[' + index + '][cnic_front]', 'card')" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'cnic_front') ? 'border-red-400' : ''">
                                            Scan Front
                                        </button>
                                        <button type="button" @click="document.getElementById('file_guarantor_' + index + '_cnic_front').click()" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'cnic_front') ? 'border-red-400' : ''">
                                            Upload
                                        </button>
                                    </div>
                                    
                                    <input type="file" :name="'guarantors[' + index + '][cnic_front]'" :id="'file_guarantor_' + index + '_cnic_front'" accept="image/*" @change="updateGuarantorPreview($event, index, 'cnic_front_url')" class="hidden">
                                    <input type="hidden" :name="'guarantors[' + index + '][delete_cnic_front]'" :value="g.delete_cnic_front ? '1' : '0'">
                                    <p x-show="getError(index, 'cnic_front')" x-text="getError(index, 'cnic_front')" class="{{ $error }} text-[10px]"></p>
                                </div>

                                {{-- CNIC Back --}}
                                <div class="space-y-2">
                                    <label class="{{ $label }} text-xs">CNIC Back</label>
                                    
                                    {{-- Preview Card --}}
                                    <div x-show="g.cnic_back_url" class="relative group w-full h-24 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm"
                                         :class="getError(index, 'cnic_back') ? 'border-red-400' : ''">
                                        <img :src="g.cnic_back_url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-1.5">
                                            <a :href="g.cnic_back_url" target="_blank" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="View Large">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="startCamera('guarantors[' + index + '][cnic_back]', 'card')" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="Retake Photo">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="clearGuarantorFile(index, 'cnic_back', 'cnic_back_url')" class="p-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Control Buttons --}}
                                    <div x-show="!g.cnic_back_url" class="flex flex-col gap-1.5">
                                        <button type="button" @click="startCamera('guarantors[' + index + '][cnic_back]', 'card')" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'cnic_back') ? 'border-red-400' : ''">
                                            Scan Back
                                        </button>
                                        <button type="button" @click="document.getElementById('file_guarantor_' + index + '_cnic_back').click()" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'cnic_back') ? 'border-red-400' : ''">
                                            Upload
                                        </button>
                                    </div>
                                    
                                    <input type="file" :name="'guarantors[' + index + '][cnic_back]'" :id="'file_guarantor_' + index + '_cnic_back'" accept="image/*" @change="updateGuarantorPreview($event, index, 'cnic_back_url')" class="hidden">
                                    <input type="hidden" :name="'guarantors[' + index + '][delete_cnic_back]'" :value="g.delete_cnic_back ? '1' : '0'">
                                    <p x-show="getError(index, 'cnic_back')" x-text="getError(index, 'cnic_back')" class="{{ $error }} text-[10px]"></p>
                                </div>

                                {{-- Visiting Card --}}
                                <div class="space-y-2">
                                    <label class="{{ $label }} text-xs">Visiting Card</label>
                                    
                                    {{-- Preview Card --}}
                                    <div x-show="g.visiting_card_url" class="relative group w-full h-24 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm"
                                         :class="getError(index, 'visiting_card') ? 'border-red-400' : ''">
                                        <img :src="g.visiting_card_url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-1.5">
                                            <a :href="g.visiting_card_url" target="_blank" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="View Large">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="startCamera('guarantors[' + index + '][visiting_card]', 'card')" class="p-1 bg-white text-gray-800 rounded hover:bg-gray-100 transition-colors" title="Retake Photo">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="clearGuarantorFile(index, 'visiting_card', 'visiting_card_url')" class="p-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Control Buttons --}}
                                    <div x-show="!g.visiting_card_url" class="flex flex-col gap-1.5">
                                        <button type="button" @click="startCamera('guarantors[' + index + '][visiting_card]', 'card')" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'visiting_card') ? 'border-red-400' : ''">
                                            Scan Card
                                        </button>
                                        <button type="button" @click="document.getElementById('file_guarantor_' + index + '_visiting_card').click()" 
                                                class="inline-flex items-center justify-center gap-1 rounded border border-gray-300 bg-white py-1 px-1.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                                :class="getError(index, 'visiting_card') ? 'border-red-400' : ''">
                                            Upload
                                        </button>
                                    </div>
                                    
                                    <input type="file" :name="'guarantors[' + index + '][visiting_card]'" :id="'file_guarantor_' + index + '_visiting_card'" accept="image/*,application/pdf" @change="updateGuarantorPreview($event, index, 'visiting_card_url')" class="hidden">
                                    <input type="hidden" :name="'guarantors[' + index + '][delete_visiting_card]'" :value="g.delete_visiting_card ? '1' : '0'">
                                    <p x-show="getError(index, 'visiting_card')" x-text="getError(index, 'visiting_card')" class="{{ $error }} text-[10px]"></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>

                {{-- Add Guarantor Button --}}
                <div class="flex justify-center">
                    <button type="button" @click="addGuarantor()"
                        class="inline-flex items-center gap-2 rounded-lg border border-dashed border-brand-400 px-5 py-2.5 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/10 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Another Guarantor
                    </button>
                </div>

                {{-- Nav --}}
                <div class="flex items-center justify-between pt-2 gap-3">
                    <a href="{{ route('tenants.showStep', [$tenant, 1]) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back
                    </a>

                    <div class="flex items-center gap-3">
                        {{-- Save Only --}}
                        <button type="submit" name="save_only" value="1"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Only
                        </button>
                        {{-- Continue --}}
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                            Continue — Step 3
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Reusable Camera Scanner Overlay Modal --}}
                <div x-show="showCameraModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
                     style="display: none;">
                    
                    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white" 
                                x-text="activeGuide === 'face' ? 'Live Portrait Photo Capture' : 'Scan Document'">
                            </h3>
                            <button type="button" @click="closeCamera()" class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Camera Viewport --}}
                        <div class="relative bg-gray-950 aspect-[4/3] flex items-center justify-center overflow-hidden">
                            <video id="camera_preview_video" 
                                   class="w-full h-full object-cover transform"
                                   :class="activeGuide === 'face' ? '-scale-x-100' : ''"
                                   autoplay playsinline muted>
                            </video>
                            
                            {{-- Silhouette Guides --}}
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                {{-- Face oval guide --}}
                                <div x-show="activeGuide === 'face'" class="w-[60%] h-[75%] rounded-[50%] border-2 border-dashed border-brand-400 bg-black/40 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                                {{-- Card rectangle guide --}}
                                <div x-show="activeGuide === 'card'" class="w-[75%] h-[63%] rounded-xl border-2 border-dashed border-brand-400 bg-black/40 shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                            </div>
                            
                            {{-- Overlay Status --}}
                            <div x-show="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/80 text-white">
                                <svg class="animate-spin h-8 w-8 text-brand-500 mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm">Initializing camera...</span>
                            </div>
                            
                            <div x-show="cameraError" class="absolute inset-0 p-6 flex flex-col items-center justify-center bg-gray-950/90 text-center text-white">
                                <svg class="h-10 w-10 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm font-medium" x-text="cameraError"></p>
                            </div>
                        </div>
                        
                        {{-- Modal Footer --}}
                        <div class="flex items-center justify-between bg-gray-50 px-5 py-4 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="closeCamera()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                Cancel
                            </button>
                            
                            <div class="flex gap-2">
                                <button type="button" x-show="cameraDevices.length > 1" @click="toggleCamera()" 
                                        class="rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                                        title="Switch Camera">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                                    </svg>
                                </button>
                                
                                <button type="button" @click="takeSnapshot()" 
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-600 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Capture Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('guarantorManager', (initial, serverErrors) => {
                    const formatCnic = (value) => {
                        if (!value) return '';
                        const digits = value.replace(/\D/g, '').slice(0, 13);
                        if (digits.length <= 5)  return digits;
                        if (digits.length <= 12) return digits.slice(0, 5) + '-' + digits.slice(5);
                        return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12, 13);
                    };
                    const initialArray = Array.isArray(initial) ? initial : Object.values(initial || {});
                    return {
                        guarantors: initialArray.map(g => ({
                            ...g,
                            cnic: formatCnic(g.cnic || ''),
                            delete_cnic_front: g.delete_cnic_front || false,
                            delete_cnic_back: g.delete_cnic_back || false,
                            delete_photo: g.delete_photo || false,
                            delete_visiting_card: g.delete_visiting_card || false
                        })),
                        errors: Array.isArray(serverErrors) ? {} : (serverErrors || {}),
                        getError(index, field) {
                            const key = `guarantors.${index}.${field}`;
                            return this.errors[key] ? this.errors[key][0] : null;
                        },
                        formatGuarantorCnic(value) {
                            return formatCnic(value);
                        },
                        
                        // Camera State
                        cameraStream: null,
                        activeInputName: '',
                        activeGuide: 'card',
                        showCameraModal: false,
                        cameraDevices: [],
                        currentCameraId: null,
                        cameraLoading: false,
                        cameraError: '',

                        addGuarantor() {
                            this.guarantors.push({
                                name: '', cnic: '', relation: '', phone: '',
                                address: '', occupation: '', shop_name: '',
                                cnic_front_url: '', cnic_back_url: '', photo_url: '', visiting_card_url: '',
                                delete_cnic_front: false, delete_cnic_back: false, delete_photo: false, delete_visiting_card: false
                            });
                        },
                        removeGuarantor(index) {
                            if (this.guarantors.length > 1) {
                                this.guarantors.splice(index, 1);
                            }
                        },

                        // Previews & Deletion Helpers
                        updateGuarantorPreview(event, index, field) {
                            const file = event.target.files[0];
                            if (file) {
                                this.guarantors[index][field] = URL.createObjectURL(file);
                                
                                // Clear deletion flag
                                const deleteField = 'delete_' + field.replace('_url', '');
                                this.guarantors[index][deleteField] = false;
                            }
                        },
                        clearGuarantorFile(index, inputName, field) {
                            const inputEl = document.querySelector(`[name="guarantors[${index}][${inputName}]"]`);
                            if (inputEl) {
                                inputEl.value = '';
                            }
                            this.guarantors[index][field] = '';
                            
                            // Set deletion flag to true
                            const deleteField = 'delete_' + inputName;
                            this.guarantors[index][deleteField] = true;
                        },

                        // Camera Scanner Methods
                        async startCamera(inputName, guideType) {
                            this.activeInputName = inputName;
                            this.activeGuide = guideType;
                            this.showCameraModal = true;
                            this.cameraLoading = true;
                            this.cameraError = '';
                            
                            if (this.cameraStream) {
                                this.cameraStream.getTracks().forEach(track => track.stop());
                                this.cameraStream = null;
                            }
                            
                            try {
                                const devices = await navigator.mediaDevices.enumerateDevices();
                                this.cameraDevices = devices.filter(d => d.kind === 'videoinput');
                                
                                let constraints = {
                                    video: {
                                        width: { ideal: 1280 },
                                        height: { ideal: 720 }
                                    }
                                };
                                
                                if (this.currentCameraId) {
                                    constraints.video.deviceId = { exact: this.currentCameraId };
                                } else {
                                    constraints.video.facingMode = guideType === 'card' ? 'environment' : 'user';
                                }
                                
                                this.cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                                
                                const activeTrack = this.cameraStream.getVideoTracks()[0];
                                if (activeTrack && activeTrack.getSettings) {
                                    const settings = activeTrack.getSettings();
                                    if (settings.deviceId && !this.currentCameraId) {
                                        this.currentCameraId = settings.deviceId;
                                    }
                                }
                                
                                const videoEl = document.getElementById('camera_preview_video');
                                if (videoEl) {
                                    videoEl.srcObject = this.cameraStream;
                                    videoEl.play().catch(e => console.error("Error playing video:", e));
                                }
                                this.cameraLoading = false;
                            } catch (err) {
                                console.error("Error accessing camera:", err);
                                this.cameraError = "Could not access camera. Please verify camera permissions and secure context (HTTPS).";
                                this.cameraLoading = false;
                            }
                        },
                        
                        async toggleCamera() {
                            if (this.cameraDevices.length <= 1) return;
                            const currentIndex = this.cameraDevices.findIndex(d => d.deviceId === this.currentCameraId);
                            const nextIndex = (currentIndex + 1) % this.cameraDevices.length;
                            this.currentCameraId = this.cameraDevices[nextIndex].deviceId;
                            await this.startCamera(this.activeInputName, this.activeGuide);
                        },
                        
                        takeSnapshot() {
                            try {
                                console.log("Capture button clicked");
                                const videoEl = document.getElementById('camera_preview_video');
                                if (!videoEl) {
                                    alert("Error: Video preview element not found!");
                                    return;
                                }
                                
                                const videoWidth = videoEl.videoWidth;
                                const videoHeight = videoEl.videoHeight;
                                if (!videoWidth || !videoHeight) {
                                    alert("Error: Video stream is not fully loaded yet.");
                                    return;
                                }
                                
                                const displayWidth = videoEl.offsetWidth;
                                const displayHeight = videoEl.offsetHeight;
                                
                                const scale = Math.max(displayWidth / videoWidth, displayHeight / videoHeight);
                                const renderedWidth = videoWidth * scale;
                                const renderedHeight = videoHeight * scale;
                                
                                const offsetX = (renderedWidth - displayWidth) / 2;
                                const offsetY = (renderedHeight - displayHeight) / 2;
                                
                                const pctW = this.activeGuide === 'face' ? 0.60 : 0.75;
                                const pctH = this.activeGuide === 'face' ? 0.75 : 0.63;
                                
                                const guideWidth = displayWidth * pctW;
                                const guideHeight = displayHeight * pctH;
                                
                                const guideX = (displayWidth - guideWidth) / 2;
                                const guideY = (displayHeight - guideHeight) / 2;
                                
                                let cropX = (guideX + offsetX) / scale;
                                let cropY = (guideY + offsetY) / scale;
                                let cropW = guideWidth / scale;
                                let cropH = guideHeight / scale;
                                
                                cropX = Math.max(0, Math.min(videoWidth, cropX));
                                cropY = Math.max(0, Math.min(videoHeight, cropY));
                                cropW = Math.max(1, Math.min(videoWidth - cropX, cropW));
                                cropH = Math.max(1, Math.min(videoHeight - cropY, cropH));
                                
                                const canvas = document.createElement('canvas');
                                canvas.width = cropW;
                                canvas.height = cropH;
                                const ctx = canvas.getContext('2d');
                                
                                if (this.activeGuide === 'face') {
                                    ctx.translate(cropW, 0);
                                    ctx.scale(-1, 1);
                                }
                                
                                ctx.drawImage(
                                    videoEl, 
                                    cropX, cropY, cropW, cropH,
                                    0, 0, cropW, cropH
                                );
                                
                                canvas.toBlob((blob) => {
                                    try {
                                        if (!blob) {
                                            alert("Error: Failed to capture camera image blob.");
                                            return;
                                        }
                                        
                                        const cleanInputName = this.activeInputName.replace(/[^a-zA-Z0-9_]/g, '_');
                                        let file = new File([blob], `${cleanInputName}_captured.jpg`, { type: 'image/jpeg' });
                                        
                                        const inputEl = document.querySelector(`[name="${this.activeInputName}"]`);
                                        if (!inputEl) {
                                            alert(`Error: Target input element [name="${this.activeInputName}"] not found on the page!`);
                                            return;
                                        }
                                        
                                        const dt = new DataTransfer();
                                        dt.items.add(file);
                                        inputEl.files = dt.files;
                                        
                                        // Clear deletion flag
                                        const match = this.activeInputName.match(/guarantors\[(\d+)\]\[(\w+)\]/);
                                        if (match) {
                                            const index = parseInt(match[1]);
                                            const field = match[2];
                                            const deleteField = 'delete_' + field;
                                            this.guarantors[index][deleteField] = false;
                                            
                                            // Update preview URL
                                            this.guarantors[index][field + '_url'] = URL.createObjectURL(file);
                                        }
                                        
                                        inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                                        this.closeCamera();
                                    } catch (innerErr) {
                                        alert("Error inside camera callback: " + innerErr.message);
                                    }
                                }, 'image/jpeg', 0.9);
                            } catch (outerErr) {
                                alert("Error during capture: " + outerErr.message);
                            }
                        },
                        
                        closeCamera() {
                            if (this.cameraStream) {
                                this.cameraStream.getTracks().forEach(track => track.stop());
                                this.cameraStream = null;
                            }
                            this.showCameraModal = false;
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce