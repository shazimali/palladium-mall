@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Import Flats/Shops" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Left 2 columns: Upload Form -->
        <div class="xl:col-span-2 space-y-6">
            <x-common.component-card title="Upload CSV File" desc="Select a CSV file containing unit information to import them in bulk.">
                @if (session('import_errors'))
                    <div class="mb-6 rounded-lg bg-red-50 p-4 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                        <div class="flex items-start gap-3">
                            <span class="text-xl text-red-500">❌</span>
                            <div class="w-full">
                                <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                                    Import Failed: Validation Errors Found
                                </h3>
                                <p class="mt-1 text-xs text-red-700 dark:text-red-400">
                                    Please correct the errors listed below in your CSV file and try again. No data has been imported.
                                </p>
                                <div class="mt-3 max-h-60 overflow-y-auto space-y-2 border-t border-red-200/50 dark:border-red-800/50 pt-3">
                                    @foreach (session('import_errors') as $row => $rowErrors)
                                        <div class="text-xs">
                                            <span class="font-bold text-red-700 dark:text-red-300">Row {{ $row }}:</span>
                                            <ul class="list-disc pl-5 mt-0.5 space-y-0.5 text-gray-700 dark:text-gray-400">
                                                @foreach ($rowErrors as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                        <div class="flex items-start gap-3">
                            <span class="text-xl text-red-500">❌</span>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                                    Upload Error
                                </h3>
                                <ul class="list-disc pl-5 mt-1 text-xs text-red-700 dark:text-red-400">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('units.import.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Drag & Drop Zone -->
                    <div x-data="{
                            dragOver: false,
                            fileName: '',
                            handleFile(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    this.fileName = file.name;
                                }
                            }
                         }"
                         class="relative">
                        <label class="flex flex-col items-center justify-center border-2 border-dashed rounded-xl p-8 cursor-pointer transition-all duration-200"
                               :class="dragOver ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/20' : 'border-gray-300 hover:border-brand-400 bg-white dark:border-gray-700 dark:bg-gray-950'"
                               @dragover.prevent="dragOver = true"
                               @dragleave.prevent="dragOver = false"
                               @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; handleFile({target: $refs.fileInput})">
                            
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-4xl mb-3">📁</span>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    <span class="text-brand-500">Click to upload</span> or drag and drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    CSV file only (max 10MB)
                                </p>
                            </div>
                            
                            <input x-ref="fileInput" id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" class="hidden" @change="handleFile">
                        </label>
                        
                        <!-- File details display -->
                        <div x-show="fileName" x-cloak class="mt-4 flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-sm">
                            <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <span>📄</span>
                                <span class="font-medium" x-text="fileName"></span>
                            </div>
                            <button type="button" @click="fileName = ''; $refs.fileInput.value = ''" class="text-xs font-semibold text-red-500 hover:text-red-600">
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <span>🚀</span>
                            Import CSV
                        </button>
                        <a href="{{ route('units.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        <!-- Right 1 column: Template Download & Help -->
        <div class="space-y-6">
            <x-common.component-card title="CSV Templates" desc="Download the sample template to start.">
                <div class="space-y-4">
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Use our official template to prepare your unit data. This ensures all headers match the database fields exactly.
                    </p>
                    <a href="{{ route('units.import.template') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-100 hover:bg-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 transition-colors">
                        <span>📥</span>
                        Download CSV Template
                    </a>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Data Guide" desc="CSV Column Formats">
                <div class="space-y-3.5 max-h-[400px] overflow-y-auto pr-1">
                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">unit_number <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Unique alphanumeric code (e.g. A-101, S-G01).</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">floor <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Name of the floor. Created dynamically if not found (e.g. 1st, Ground).</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">block <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Name of the block. Created dynamically if not found (e.g. Abubakar, Usman).</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">area <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Name of the area/zone. Created dynamically if not found (e.g. Single, Double).</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">type <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Allowed values: <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">flat</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">shop</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">office</code>.</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">status <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Allowed values: <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">vacant</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">occupied</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-brand-500">sold</code>.</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">landlord_name <span class="text-red-500">*</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Full name of the landlord. Must match an existing landlord exactly.</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">area_sqft <span class="text-gray-400">(Optional)</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Size of the unit in square feet. Must be numeric.</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">date <span class="text-gray-400">(Optional)</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Creation date in format YYYY-MM-DD. Defaults to today's date if empty.</p>
                    </div>

                    <div class="text-xs border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-bold text-gray-800 dark:text-gray-200">electricity_meter / water_meter / gas_meter <span class="text-gray-400">(Optional)</span></span>
                        <p class="mt-0.5 text-gray-600 dark:text-gray-400">Active meter reference numbers. Created and linked to the unit if provided.</p>
                    </div>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
