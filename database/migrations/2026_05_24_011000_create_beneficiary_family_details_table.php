<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql_jblbV2';
    protected $schema = 'pension';
    protected $schemeIds = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 13, 17, 19, 20];
    protected $isCleans = [1, 2, 10];

    public function up(): void
    {
        $conn = DB::connection($this->connection);

        // 1. Create base table
        $conn->statement("
            CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_family_details
            (
                scheme_id integer NOT NULL,
                application_id text NOT NULL,
                beneficiary_id text,
                family_members jsonb,
                is_clean smallint NOT NULL DEFAULT 1,
                created_at timestamp without time zone,
                updated_at timestamp without time zone,
                ip_address varchar(250),
                otp_validation_id integer
            ) PARTITION BY LIST (scheme_id)
        ");

        // 2. Create partitions
        foreach ($this->schemeIds as $schemeId) {
            $conn->statement("
                CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_family_details_{$schemeId}
                PARTITION OF {$this->schema}.beneficiary_family_details
                FOR VALUES IN ({$schemeId})
                PARTITION BY LIST (is_clean)
            ");

            foreach ($this->isCleans as $isClean) {
                $conn->statement("
                    CREATE TABLE IF NOT EXISTS {$this->schema}.beneficiary_family_details_{$schemeId}_{$isClean}
                    PARTITION OF {$this->schema}.beneficiary_family_details_{$schemeId}
                    FOR VALUES IN ({$isClean})
                ");
            }
        }
    }

    public function down(): void
    {
        $conn = DB::connection($this->connection);

        foreach ($this->schemeIds as $schemeId) {
            foreach ($this->isCleans as $isClean) {
                $conn->statement("DROP TABLE IF EXISTS {$this->schema}.beneficiary_family_details_{$schemeId}_{$isClean} CASCADE");
            }
            $conn->statement("DROP TABLE IF EXISTS {$this->schema}.beneficiary_family_details_{$schemeId} CASCADE");
        }
        $conn->statement("DROP TABLE IF EXISTS {$this->schema}.beneficiary_family_details CASCADE");
    }
};
