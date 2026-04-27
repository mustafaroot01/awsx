<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand Policies Table
        Schema::table('policies', function (Blueprint $table) {
            if (!Schema::hasColumn('policies', 'trade_name')) {
                $table->string('trade_name')->nullable()->after('client_name');
            }
            if (!Schema::hasColumn('policies', 'permanent_address')) {
                $table->string('permanent_address')->nullable()->after('trade_name');
            }
            if (!Schema::hasColumn('policies', 'phone')) {
                $table->string('phone')->nullable()->after('permanent_address');
            }
            
            // Location Detail
            if (!Schema::hasColumn('policies', 'district')) {
                $table->string('district')->nullable();
            }
            if (!Schema::hasColumn('policies', 'shop_no')) {
                $table->string('shop_no')->nullable();
            }
            if (!Schema::hasColumn('policies', 'street_region')) {
                $table->string('street_region')->nullable();
            }
            if (!Schema::hasColumn('policies', 'shop_phone')) {
                $table->string('shop_phone')->nullable();
            }
            
            // AML Signature
            if (!Schema::hasColumn('policies', 'aml_signed_at')) {
                $table->timestamp('aml_signed_at')->nullable()->after('aml_officer_name');
            }
        });

        // 2. Fire & Theft Specific Questionnaire
        if (!Schema::hasTable('fire_theft_details')) {
            Schema::create('fire_theft_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('policy_id')->constrained()->onDelete('cascade');
                
                $table->boolean('is_owner')->default(true);
                $table->boolean('has_accounting_records')->default(false);
                $table->string('jewelry_storage')->nullable();
                $table->boolean('is_insured_amount_real')->default(true);
                $table->string('closing_duration')->nullable();
                $table->string('guarding_nature')->nullable();
                
                $table->text('previous_incidents')->nullable();
                $table->text('neighbors_incidents')->nullable();
                $table->string('hazardous_materials')->nullable();
                $table->text('previous_insurance_history')->nullable();
                
                // Boolean flags for Perils (Question 14)
                $table->boolean('peril_explosion')->default(false);
                $table->boolean('peril_flood')->default(false);
                $table->boolean('peril_storm')->default(false);
                $table->boolean('peril_riot')->default(false);
                $table->boolean('peril_tank_overflow')->default(false);
                $table->boolean('peril_self_combustion')->default(false);
                $table->boolean('peril_aircraft_impact')->default(false);
                $table->boolean('peril_earthquake')->default(false);

                $table->timestamps();
            });
        }

        // 3. Technical Inspection Report Expansion
        Schema::table('inspection_reports', function (Blueprint $table) {
            $cols = [
                'construction_description' => 'text',
                'wall_material' => 'string',
                'roof_material' => 'string',
                'floor_material' => 'string',
                'neighbors_connectivity' => 'boolean',
                'neighbors_nature' => 'string',
                'doors_locks_type' => 'string',
                'window_grids' => 'boolean',
                'lighting_heating' => 'string',
                'machine_fuel' => 'string',
                'wood_layers' => 'boolean',
                'water_source' => 'string',
                'extinguishers' => 'string',
                'electrical_state' => 'string',
                'hazardous_observation' => 'string',
                'waste_disposal' => 'string',
                'inspector_recommendation' => 'text',
                'sketch_path' => 'string'
            ];

            foreach ($cols as $col => $type) {
                if (!Schema::hasColumn('inspection_reports', $col)) {
                    if ($type === 'text') $table->text($col)->nullable();
                    elseif ($type === 'boolean') $table->boolean($col)->default(false);
                    else $table->string($col)->nullable();
                }
            }
        });

        // 4. Life Insurance - Health Statement Expansion
        Schema::table('life_policy_details', function (Blueprint $table) {
             $cols = [
                'marital_status' => 'string',
                'document_type' => 'string',
                'id_card_no' => 'string',
                'issue_authority_date' => 'string',
                'spouse_name' => 'string',
                'work_address' => 'string',
                'home_address_detail' => 'string',
                'height_cm' => 'integer',
                'weight_kg' => 'integer',
                'weight_change_last_year' => 'string',
                'health_questionnaire' => 'json'
            ];

            foreach ($cols as $col => $type) {
                if (!Schema::hasColumn('life_policy_details', $col)) {
                    if ($type === 'integer') $table->integer($col)->nullable();
                    elseif ($type === 'json') $table->json($col)->nullable();
                    else $table->string($col)->nullable();
                }
            }
        });

        // 5. Company/KYB Details
        if (!Schema::hasTable('company_details')) {
            Schema::create('company_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('policy_id')->constrained()->onDelete('cascade');
                
                $table->string('authorized_name')->nullable();
                $table->string('authorized_address')->nullable();
                $table->text('founder_names')->nullable();
                $table->string('manager_name')->nullable();
                $table->string('board_chairman')->nullable();
                $table->text('board_members')->nullable();
                $table->text('shareholder_names')->nullable();
                $table->string('activity_type')->nullable(); // Industrial, etc.
                $table->date('founding_date')->nullable();
                $table->decimal('capital', 20, 2)->default(0);
                $table->string('founding_place')->nullable();
                $table->string('external_auditor_name')->nullable();
                
                $table->timestamps();
            });
        }

        // 6. Beneficiaries Expansion
        Schema::table('policy_beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('policy_beneficiaries', 'name_quad')) {
                $table->string('name_quad')->nullable(); 
            }
            if (!Schema::hasColumn('policy_beneficiaries', 'share_survival')) {
                $table->decimal('share_survival', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('policy_beneficiaries', 'share_death')) {
                $table->decimal('share_death', 5, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down methods omitted for brevity
    }
};
