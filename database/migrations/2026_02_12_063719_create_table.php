<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql_jblbV2';
    protected $schema = 'pension';

    public function up(): void
    {
        $conn = DB::connection($this->connection);

        $conn->statement("
            CREATE SCHEMA IF NOT EXISTS {$this->schema}
        ");

        /*
        |--------------------------------------------------------------------------
        | 1. unique_app_ben_ids
        |--------------------------------------------------------------------------
        */
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.unique_app_ben_ids
            (
                scheme_id integer NOT NULL DEFAULT 0,
                application_id text,
                beneficiary_id text,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                CONSTRAINT unique_app_ben_ids_pkey PRIMARY KEY (application_id),
                CONSTRAINT unique_app_ben_ids_uniquekey UNIQUE (application_id, scheme_id)

            )
        ");

        /*
        |--------------------------------------------------------------------------
        | 2. beneficiary_personals (Partitioned)
        |--------------------------------------------------------------------------
        */
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_personals
            (
                scheme_id integer NOT NULL,
                application_id text NOT NULL,
                beneficiary_id text,
                application_type varchar(100),
                application_date date,
                ds_registration_no varchar(100),
                ds_date date,
                ds_phase integer,
                beneficiary_name varchar(250),
                age integer,
                email varchar(250),
                aadhar_no varchar(12),
                dob date,
                gender smallint,
                mobile_no character(10),
                father_fname varchar(250),
                father_mname varchar(250),
                father_lname varchar(250),
                father_fullname varchar(250),
                mother_fname varchar(250),
                mother_mname varchar(250),
                mother_lname varchar(250),
                mother_fullname varchar(250),
                martial_status integer,
                spouse_fname varchar(250),
                spouse_mname varchar(250),
                spouse_lname varchar(250),
                spouse_fullname varchar(250),
                caste smallint,
                caste_certificate_no varchar(250),
                ip_address varchar(250),
                next_level_role_id smallint,
                is_final smallint default 0,
                other_details jsonb,
                created_by integer,
                updated_by integer,
                is_clean smallint,
                marked_data smallint,
                jnmp_marked smallint,
                jnmp_remarks varchar(255),
                reactive_reason varchar(255),
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                otp_validation_id integer
            ) PARTITION BY LIST (scheme_id)
        ");

        /*
        |--------------------------------------------------------------------------
        | 3. beneficiary_contacts (Partitioned)
        |--------------------------------------------------------------------------
        */
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_contacts
            (
                scheme_id integer NOT NULL,
                application_id text,
                beneficiary_id text,
                state_id integer,
                district_id integer,
                rural_urban smallint,
                blockurban integer,
                gpward integer,
                policestation varchar(100),
                village_town_city varchar(100),
                house_premise_no varchar(100),
                post_office varchar(100),
                pincode char(6),
                ip_address varchar(250),
                other_details jsonb,
                is_clean smallint,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                created_by_dist_code integer,
                created_by_local_body_code integer,
                otp_validation_id integer
            ) PARTITION BY LIST (scheme_id)
        ");

        /*
        |--------------------------------------------------------------------------
        | 4. beneficiary_bank (Partitioned)
        |--------------------------------------------------------------------------
        */
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_banks
            (
                scheme_id integer NOT NULL,
                application_id text NOT NULL,
                beneficiary_id bigint,
                ifscode varchar(25),
                bankaccountnumber varchar(30),
                other_details jsonb,
                is_clean smallint NOT NULL DEFAULT 1,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                ip_address varchar(250),
                otp_validation_id integer
            ) PARTITION BY LIST (scheme_id)
        ");

        /*
        |--------------------------------------------------------------------------
        | 5. beneficiary_aadhar (Partitioned)
        |--------------------------------------------------------------------------
        */
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_aadhars
            (
                scheme_id integer NOT NULL,
                application_id text NOT NULL,
                beneficiary_id text,
                encode_key text,
                encoded_aadhar text,
                aadhar_vault text,
                aadhar_hash varchar(255),
                is_clean smallint DEFAULT 1,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                 ip_address varchar(250),
                otp_validation_id integer
            ) PARTITION BY LIST (scheme_id)

        ");

        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_documents(
            id bigserial,
            scheme_id integer not null,
            beneficiary_id text,
            application_id text NOT NULL,
            attched_document TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            document_extension VARCHAR(50) NOT NULL,
            document_mime_type VARCHAR(150) NOT NULL,
            document_type SMALLINT NOT NULL,
            created_by INTEGER NOT NULL,
            tab_code INTEGER NULL,
            is_clean smallint DEFAULT 1,
            created_at TIMESTAMP WITHOUT TIME ZONE,
            updated_at TIMESTAMP WITHOUT TIME ZONE,
            otp_validation_id integer
        ) PARTITION BY LIST (scheme_id);
        ");
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_self_declarations(
            scheme_id integer NOT NULL,
            application_id text NOT NULL,
            beneficiary_id text,
            is_resident boolean,
            earn_monthly_remuneration boolean,
            info_genuine_decl boolean,
            other_details jsonb,
            is_clean smallint DEFAULT 1,
            created_at timestamp without time zone,
            updated_at timestamp without time zone,
            otp_validation_id integer,
            ip_address VARCHAR(45) 
        ) PARTITION BY LIST (scheme_id);");
    }

    public function down(): void
    {
        $conn = DB::connection($this->connection);

        $tables = [
            "beneficiary_self_declarations",
            'beneficiary_documents',
            'beneficiary_aadhars',
            'beneficiary_banks',
            'beneficiary_contacts',
            'beneficiary_personals',
            'unique_app_ben_ids',
        ];

        foreach ($tables as $table) {
            $conn->statement("
                DROP TABLE IF EXISTS {$this->schema}.{$table} CASCADE
            ");
        }
    }
};
