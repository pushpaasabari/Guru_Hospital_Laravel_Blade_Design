/**
 * Shared classification for Natural / Normal Vaginal Delivery vs Surgical (LSCS / OT) cases
 * matching Admission::isNaturalDelivery() on the backend.
 */
export interface DeliveryLike {
  surgery_type?: string | null;
  procedure_name?: string | null;
  procedure_done?: string | null;
}

const SURGICAL_OB_KEYWORDS = /\b(lscs|caesarean|cesarean|c-section|hysterectomy|laparotomy|laparoscop|myomectomy|salpingectomy|oophorectomy|cerclage)\b/i;
const NATURAL_DELIVERY_KEYWORDS = /\b(natural|normal|ftnd|vaginal|nvd|svd|spontaneous|breech|forceps|vacuum|ventouse|labour\s*natural|labor\s*natural)\b/i;

export function isNaturalDeliveryCase(admission?: DeliveryLike | null): boolean {
  if (!admission || admission.surgery_type !== 'delivery') {
    return false;
  }

  const procText = `${admission.procedure_done || ''} ${admission.procedure_name || ''}`.trim();
  if (!procText) {
    return false;
  }

  if (SURGICAL_OB_KEYWORDS.test(procText)) {
    return false;
  }

  return NATURAL_DELIVERY_KEYWORDS.test(procText);
}

export function getClinicalTeamLabels(isNaturalDelivery: boolean) {
  if (isNaturalDelivery) {
    return {
      sectionTitle: 'Labour & Delivery Team',
      primaryDoctorLabel: 'Delivered By (Obstetrician / Doctor)',
      primaryDoctorShort: 'Delivered By',
      assistantDoctorLabel: 'Assisted By (Doctor / Midwife)',
      anaesthetistLabel: 'Anaesthetist (If Epidural / OT)',
      assistedAnaesthetistLabel: 'Assisted Anaesthetist (Optional)',
      anaesthesiaTypeLabel: 'Analgesia / Pain Relief (Optional)',
      staffNurseLabel: 'Labour / Delivery Staff Nurse',
      otTechLabel: 'Labour Room / OT Assistant',
      pediatricianLabel: 'Pediatrician / Neonatologist',
      otherHelpLabel: 'Other Clinical / Nursing Help',
    };
  }

  return {
    sectionTitle: 'Surgical & Anaesthesia Team',
    primaryDoctorLabel: 'Surgeon',
    primaryDoctorShort: 'Surgeon',
    assistantDoctorLabel: 'Assisted Surgeon',
    anaesthetistLabel: 'Anaesthetist',
    assistedAnaesthetistLabel: 'Assisted Anaesthetist',
    anaesthesiaTypeLabel: 'Anaesthesia Type',
    staffNurseLabel: 'Scrub / Staff Nurse',
    otTechLabel: 'OT Technician',
    pediatricianLabel: 'Pediatrician / Neonatologist',
    otherHelpLabel: 'Assistant / Other Clinical Help',
  };
}
