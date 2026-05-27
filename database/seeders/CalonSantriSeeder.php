<?php

namespace Database\Seeders;

use App\Models\ErpAccount;
use App\Models\PpdbRegistration;
use App\Models\PpdbDocument;
use Illuminate\Database\Seeder;

class CalonSantriSeeder extends Seeder
{
    public function run(): void
    {
        $calonSantri = [
            [
                'student_name' => 'Ahmad Fauzi Rahman',
                'nik' => '3201010101080001',
                'gender' => 'L',
                'birth_date' => '2014-03-15',
                'birth_place' => 'Bandung',
                'address' => 'Jl. Merdeka No. 10, Kec. Coblong, Bandung',
                'origin_school' => 'SDN 1 Bandung',
                'parent_name' => 'Haji Rahman',
                'parent_phone' => '081234567801',
                'parent_email' => 'rahman@example.com',
            ],
            [
                'student_name' => 'Siti Aisyah Putri',
                'nik' => '3201010101080002',
                'gender' => 'P',
                'birth_date' => '2014-07-22',
                'birth_place' => 'Sumedang',
                'address' => 'Jl. Prabu Gajah Agung No. 5, Sumedang',
                'origin_school' => 'SDN 2 Sumedang',
                'parent_name' => 'Ustadz Mulyadi',
                'parent_phone' => '081234567802',
                'parent_email' => 'mulyadi@example.com',
            ],
            [
                'student_name' => 'Muhammad Rizki Pratama',
                'nik' => '3201010101080003',
                'gender' => 'L',
                'birth_date' => '2013-11-08',
                'birth_place' => 'Garut',
                'address' => 'Jl. Ciledug No. 88, Garut',
                'origin_school' => 'MI Al-Hidayah Garut',
                'parent_name' => 'Bapak Pratama',
                'parent_phone' => '081234567803',
                'parent_email' => 'pratama@example.com',
            ],
            [
                'student_name' => 'Fatimah Az-Zahra',
                'nik' => '3201010101080004',
                'gender' => 'P',
                'birth_date' => '2014-01-30',
                'birth_place' => 'Cirebon',
                'address' => 'Jl. Sunan Gunung Jati No. 12, Cirebon',
                'origin_school' => 'SDN 3 Cirebon',
                'parent_name' => 'Haji Abdullah',
                'parent_phone' => '081234567804',
                'parent_email' => 'abdullah@example.com',
            ],
            [
                'student_name' => 'Umar Hasan Basri',
                'nik' => '3201010101080005',
                'gender' => 'L',
                'birth_date' => '2014-05-17',
                'birth_place' => 'Tasikmalaya',
                'address' => 'Jl. Paseh No. 33, Tasikmalaya',
                'origin_school' => 'MI Mathlaul Anwar',
                'parent_name' => 'Bapak Basri',
                'parent_phone' => '081234567805',
                'parent_email' => 'basri@example.com',
            ],
            [
                'student_name' => 'Khadijah Nurul Aini',
                'nik' => '3201010101080006',
                'gender' => 'P',
                'birth_date' => '2014-09-03',
                'birth_place' => 'Majalengka',
                'address' => 'Jl. Tonjong No. 7, Majalengka',
                'origin_school' => 'SDN 1 Majalengka',
                'parent_name' => 'Ibu Nurhasanah',
                'parent_phone' => '081234567806',
                'parent_email' => 'nurhasanah@example.com',
            ],
            [
                'student_name' => 'Bilal Abdurrahman',
                'nik' => '3201010101080007',
                'gender' => 'L',
                'birth_date' => '2013-12-25',
                'birth_place' => 'Kuningan',
                'address' => 'Jl. Siliwangi No. 21, Kuningan',
                'origin_school' => 'MI Persatuan Islam',
                'parent_name' => 'Ustadz Abdurrahman',
                'parent_phone' => '081234567807',
                'parent_email' => 'abdurrahman@example.com',
            ],
            [
                'student_name' => 'Hafizah Ramadhani',
                'nik' => '3201010101080008',
                'gender' => 'P',
                'birth_date' => '2014-04-10',
                'birth_place' => 'Subang',
                'address' => 'Jl. Otto Iskandardinata No. 15, Subang',
                'origin_school' => 'SDN 4 Subang',
                'parent_name' => 'Bapak Ramadhani',
                'parent_phone' => '081234567808',
                'parent_email' => 'ramadhani@example.com',
            ],
            [
                'student_name' => 'Yusuf Al-Ghazali',
                'nik' => '3201010101080009',
                'gender' => 'L',
                'birth_date' => '2014-06-28',
                'birth_place' => 'Cianjur',
                'address' => 'Jl. Raya Cipanas No. 50, Cianjur',
                'origin_school' => 'MI Nurul Huda Cianjur',
                'parent_name' => 'Haji Ghazali',
                'parent_phone' => '081234567809',
                'parent_email' => 'ghazali@example.com',
            ],
            [
                'student_name' => 'Zainab Muthmainnah',
                'nik' => '3201010101080010',
                'gender' => 'P',
                'birth_date' => '2014-02-14',
                'birth_place' => 'Purwakarta',
                'address' => 'Jl. Veteran No. 9, Purwakarta',
                'origin_school' => 'SDN 2 Purwakarta',
                'parent_name' => 'Bapak Muthmainnah',
                'parent_phone' => '081234567810',
                'parent_email' => 'muthmainnah@example.com',
            ],
        ];

        $academicYear = '2026/2027';
        $mandatoryDocs = array_keys(config('spmb.mandatory_documents', []));
        $statuses = ['pending', 'pending', 'pending', 'lulus', 'lulus', 'cadangan', 'rejected', 'pending', 'pending', 'enrolled'];
        $docStatuses = ['incomplete', 'pending_review', 'revision_needed', 'complete', 'complete', 'complete', 'complete', 'incomplete', 'pending_review', 'complete'];

        foreach ($calonSantri as $i => $data) {
            // Create ErpAccount
            $username = strtolower(str_replace(' ', '.', $data['student_name']));
            $username = substr($username, 0, 20) . '.' . rand(1000, 9999);

            $account = ErpAccount::create([
                'username' => $username,
                'full_name' => $data['student_name'],
                'email' => $data['parent_email'],
                'phone' => $data['parent_phone'],
                'password' => 'password123',
                'is_active' => true,
                'must_change_password' => true,
            ]);
            $account->assignRole('Pendaftar');

            // Create registration
            $registration = PpdbRegistration::create(array_merge($data, [
                'academic_year' => $academicYear,
                'status' => $statuses[$i],
                'source' => $i < 5 ? 'website' : 'manual',
                'document_status' => $docStatuses[$i],
                'erp_account_id' => $account->id,
            ]));

            // Upload some documents based on doc status
            $docsToUpload = match ($docStatuses[$i]) {
                'complete' => count($mandatoryDocs),       // semua dokumen
                'pending_review' => rand(5, 7),             // sebagian besar
                'revision_needed' => rand(4, 6),            // beberapa
                'incomplete' => rand(0, 3),                  // sedikit/belum
            };

            $shuffledDocs = $mandatoryDocs;
            shuffle($shuffledDocs);

            for ($j = 0; $j < $docsToUpload && $j < count($mandatoryDocs); $j++) {
                $docStatus = match ($docStatuses[$i]) {
                    'complete' => 'approved',
                    'revision_needed' => ($j === 0 ? 'rejected' : (rand(0, 1) ? 'approved' : 'pending')),
                    default => 'pending',
                };

                PpdbDocument::create([
                    'ppdb_registration_id' => $registration->id,
                    'document_type' => $shuffledDocs[$j],
                    'file_path' => "ppdb-documents/dummy-{$registration->id}-{$shuffledDocs[$j]}.pdf",
                    'status' => $docStatus,
                    'rejection_reason' => $docStatus === 'rejected' ? 'Dokumen tidak jelas, mohon upload ulang dengan kualitas lebih baik.' : null,
                    'version' => 1,
                    'verified_at' => in_array($docStatus, ['approved', 'rejected']) ? now() : null,
                    'verified_by' => in_array($docStatus, ['approved', 'rejected']) ? 1 : null,
                ]);
            }

            $this->command->info("  Calon Santri: {$data['student_name']} — status: {$statuses[$i]}, dokumen: {$docsToUpload}/{$docsToUpload}");
        }

        // Convert enrolled student role
        $enrolled = PpdbRegistration::where('status', 'enrolled')->first();
        if ($enrolled && $enrolled->account) {
            $enrolled->account->removeRole('Pendaftar');
            $enrolled->account->assignRole('Santri');
        }

        $this->command->info("\n  Total: " . count($calonSantri) . " calon santri berhasil dibuat.");
    }
}
