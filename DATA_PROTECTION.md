# Mauricare data-protection operating policy

This document is the operational companion to the public privacy notice. It is a technical baseline under the Mauritius Data Protection Act 2017, not a substitute for advice from Mauricare's appointed Data Protection Officer (DPO).

## Ownership and registration

- The board/owner must appoint a named DPO and replace the placeholder privacy email in production configuration.
- Mauricare must register as a controller with the Mauritius Data Protection Office before processing live personal data and renew the registration on time.
- The DPO owns the Record of Processing Activities (ROPA), Data Protection Impact Assessment (DPIA), processor register, international-transfer register, incident register, and annual control review.
- Material changes to purposes, recipients, countries, safeguards, or categories of data must be assessed by the DPO and notified to the Commissioner where required.

## Before production launch

1. Approve the public notice text and set `PRIVACY_NOTICE_VERSION`, controller contact, and DPO contact.
2. Complete a DPIA covering health data, matching, messaging, administrator access, documents, email, backups, and disaster recovery.
3. Record every data category, purpose, lawful basis, subject category, recipient, transfer country, safeguard, and deletion period in the ROPA.
4. Sign written data-processing agreements with hosting, mail, backup, monitoring, payment, and support providers. Confirm where each provider stores and accesses data.
5. Document the lawful condition for special-category health data and the professional-confidentiality arrangements applying to caregivers.
6. Establish an encrypted off-site backup, key-management, restore-testing, malware-scanning, vulnerability-management, and staff access-review procedure.
7. Train all staff and caregivers on confidentiality, least privilege, phishing, secure devices, and incident reporting.

## Data-subject requests

Requests sent to the configured DPO address must be logged immediately. Verify identity proportionately before disclosure. Search the database, private documents, media, mail systems, logs, and relevant processors. The DPO decides whether an exemption or legal retention duty applies and records the decision. Provide access, correction, restriction, objection, portability where applicable, or erasure without undue delay. Never silently reject a request.

Account deletion starts a configurable waiting period. The scheduled `privacy:purge-expired` command then removes documents and avatars and anonymises account, profile, health, and message content while preserving transaction records that may remain necessary for accounting or legal claims. Run `php artisan privacy:purge-expired --dry-run` to preview eligible records.

## Retention schedule

The DPO must approve concrete periods before launch. `PRIVACY_ACCOUNT_RETENTION_DAYS` controls the post-erasure waiting period; it is not the retention period for active care or accounting records. Audit retention defaults to six years and must be reconciled with Mauritian limitation, tax, employment, and health-record requirements. Backups must have a documented expiry and must not be restored without reapplying completed erasures.

## Security and access

- Health details and private documents are confidential and accessed only on a need-to-know basis.
- Administrators must provide their password and a recorded business reason before impersonation. Shared administrator accounts are prohibited.
- Production databases, volumes, backups, and administrator devices must use encryption at rest. Encryption keys must be separate from backups and rotated under a written procedure.
- Uploaded documents must be malware-scanned before staff open them. Until a scanner is integrated, uploads must be treated as untrusted and reviewed only in an isolated environment.
- Privileged accounts should use MFA. Access is reviewed at least quarterly and removed immediately on role change or departure.
- Audit data must be access-controlled and protected from ordinary user modification. It must never contain medical narrative, passwords, session tokens, or document content.

## Personal-data breach response

Anyone suspecting loss, unauthorised access, disclosure, alteration, or destruction must notify the DPO immediately. The DPO records discovery time, systems, categories, approximate people and records affected, likely consequences, containment, evidence, and remediation. Mauricare must notify the Commissioner without undue delay and, where feasible, within 72 hours of awareness. If risk to people is high, communicate clearly to affected people without undue delay unless the statutory exception is documented. Processors must contractually notify Mauricare immediately.

## Change control

Any new collection field, analytics tool, AI feature, external integration, country transfer, reuse of health information, or automated decision requires DPO review before release. High-risk changes require an updated DPIA and, where indicated, prior consultation with the Data Protection Office. Updating the public notice requires a new immutable version; existing users must be shown the new notice and asked for renewed acknowledgement where the processing basis or consent changes.
