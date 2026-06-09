<?php

namespace App\Console\Commands;

use App\Models\LegacyEmployee;
use App\Models\LegacyIndividual;
use App\Models\LegacyStudent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncGoogleEmails extends Command
{
    protected $signature = 'ieducar:sync-google-emails {file? : Caminho para o arquivo CSV (opcional para diagnóstico)} {--column=cpf : Coluna para cruzamento (cpf ou matricula)}';

    protected $description = 'Diagnóstico e sincronização de e-mails do Google Workspace';

    public function handle()
    {
        $file = $this->argument('file');

        if (!$file) {
            $this->diagnose();
            return 0;
        }

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return 1;
        }

        $this->sync($file);
        return 0;
    }

    protected function diagnose()
    {
        $this->info('Iniciando diagnóstico de alunos sem e-mail @canoasedu...');

        $results = DB::select("
            SELECT
                a.cod_aluno AS id_aluno,
                p.idpes AS id_pessoa,
                p.nome,
                f.cpf,
                pf.matricula,
                COALESCE(pf.email, p.email) AS email_atual,
                CASE
                    WHEN pf.email IS NULL THEN 'Sem usuário/email cadastrado'
                    ELSE 'Email fora do domínio @canoasedu'
                END AS motivo_falha
            FROM
                pmieducar.aluno a
            INNER JOIN
                cadastro.pessoa p ON a.ref_idpes = p.idpes
            LEFT JOIN
                cadastro.fisica f ON p.idpes = f.idpes
            LEFT JOIN
                portal.funcionario pf ON p.idpes = pf.ref_cod_pessoa_fj
            WHERE
                a.ativo = 1
                AND (
                    COALESCE(pf.email, p.email) IS NULL
                    OR COALESCE(pf.email, p.email) NOT LIKE '%@canoasedu%'
                )
            ORDER BY p.nome
        ");

        if (empty($results)) {
            $this->info('Nenhum aluno encontrado com descompasso de e-mail.');
            return;
        }

        $headers = ['ID Aluno', 'ID Pessoa', 'Nome', 'CPF', 'Matricula', 'Email Atual', 'Motivo'];
        $data = array_map(fn($r) => (array)$r, $results);

        $this->table($headers, $data);
        $this->info(count($results) . ' alunos identificados.');
    }

    protected function sync($file)
    {
        $column = $this->option('column');
        if (!in_array($column, ['cpf', 'matricula'])) {
            $this->error('Coluna inválida. Use --column=cpf ou --column=matricula');
            return;
        }

        $this->info("Iniciando sincronização via CSV usando coluna: {$column}...");

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Assume header: cpf/matricula, email

        $count = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $identifier = $row[0];
            $email = $row[1];

            if (empty($identifier) || empty($email)) continue;

            $query = LegacyEmployee::query();
            if ($column === 'cpf') {
                // CPF está em cadastro.fisica, vinculado a portal.funcionario por ref_cod_pessoa_fj
                $query->whereHas('individual', function($q) use ($identifier) {
                    $q->where('cpf', preg_replace('/\D/', '', $identifier));
                });
            } else {
                $query->where('matricula', $identifier);
            }

            $employees = $query->get();

            if ($employees->isEmpty()) {
                $this->warn("Funcionário não encontrado para {$column}: {$identifier}");
                $errors++;
                continue;
            }

            if ($employees->count() > 1) {
                $this->warn("Múltiplos registros encontrados para {$column}: {$identifier}. Ignorando para evitar erros.");
                $errors++;
                continue;
            }

            $employee = $employees->first();
            $employee->email = $email;
            $employee->save();

            // Sincroniza também em cadastro.pessoa para consistência
            $person = LegacyIndividual::find($employee->ref_cod_pessoa_fj)?->person;
            if ($person) {
                $person->email = $email;
                $person->save();
            }

            $count++;
        }

        fclose($handle);
        $this->info("Sincronização concluída: {$count} registros atualizados, {$errors} avisos/erros.");
    }
}
