# ERP Pesantren Asy-Syifaa — Changelog

## [Wave 1.5] - 2026-05-27 (In Progress)
### Added
- Full SPMB workflow integration (register → akun → dokumen → seleksi → pembayaran → enrolled)
- SpmbService & WebhookNotificationService
- Event/Listener architecture (6 events, 6 listeners)
- Portal calon santri (ProfilSaya, DokumenSaya, TagihanSaya)
- Upload bukti transfer + staff review
- Force password change on first login
- In-app notifications (5 notification classes)
- Broadcast notification (WA + in-app) dari staff
- Kirim credential WA dari dashboard staff
- API v1: /api/v1/spmb/register, status, documents
- Exam tables foundation (schema only, Wave 2)
- Project documentation (PROGRESS.md, ARCHITECTURE.md, CHANGELOG.md)

### Changed
- PpdbRegistration: + erp_account_id, document_status, profile_completed_at
- PpdbDocument: + status, rejection_reason, version
- Registration number format: PPDB-YYYY-NNNN → SPMB/YYYY/NNNN
- DokumenRelationManager: approve/reject workflow with reasons
- Resource access control via canAccess()

## [Wave 1.0] - 2026-05-26
### Added
- Laravel 13 + Filament 5 + Spatie Permission + Sanctum
- Auth: ErpAccount model, guard `erp`, 18 roles
- SPMB: PendaftarResource (CRUD + accept/reject)
- Keuangan: InvoiceResource, PaymentResource, BillingTypeResource
- User Management: ErpAccountResource
- Widgets: SpmbStatsWidget, KeuanganStatsWidget
- API: /api/spmb/sync, /api/spmb/{ref}/status
- Seeders: 18 roles, superadmin account, 7 billing types
