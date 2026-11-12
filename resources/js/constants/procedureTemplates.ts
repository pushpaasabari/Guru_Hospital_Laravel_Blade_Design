export interface ProcedureTemplate {
  id: string;
  category: 'Obstetrics & Gynaecology' | 'General & Laparoscopic Surgery' | 'Urology' | 'Orthopaedics';
  name: string;
  procedureName: string;
  procedureDone: string;
  incision: string;
  findings: string;
  anesthesiaType?: string;
  hasStent?: boolean;
  bloodLossMl?: number;
  procedureNote: string;
}

export const PROCEDURE_TEMPLATES: ProcedureTemplate[] = [
  // ─── Obstetrics & Gynaecology ──────────────────────────────────────────────
  {
    id: 'og-lscs',
    category: 'Obstetrics & Gynaecology',
    name: 'Emergency / Elective LSCS',
    procedureName: 'Lower Segment Caesarean Section (LSCS)',
    procedureDone: 'Lower Segment Caesarean Section with Pfannenstiel Incision',
    incision: 'Suprapubic Transverse (Pfannenstiel) incision',
    findings: 'Live baby delivered, thinned lower uterine segment, intact placenta',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 350,
    procedureNote:
      'Under SA, abdomen opened in layers by Pfannenstiel incision; UV fold reflected.\n' +
      'Lower uterine segment opened transversely; delivered alive baby (cried soon after birth, handed to pediatrician).\n' +
      'Placenta & membranes delivered complete; AMTSL followed.\n' +
      'Uterine incision closed in 2 layers with Vicryl 1; perfect hemostasis achieved, uterus contracted well.\n' +
      'Abdomen closed in layers; skin closed subcuticularly; no undue bleeding P/V.',
  },
  {
    id: 'og-ftnd',
    category: 'Obstetrics & Gynaecology',
    name: 'Normal Vaginal Delivery with Episiotomy',
    procedureName: 'Spontaneous Vaginal Delivery with Episiotomy',
    procedureDone: 'Normal Delivery with Right Mediolateral Episiotomy',
    incision: 'Right mediolateral episiotomy',
    findings: 'Single live mature baby delivered spontaneously, placenta complete',
    anesthesiaType: 'Local Infiltration (2% Lignocaine)',
    bloodLossMl: 150,
    procedureNote:
      'Patient in active labour; under local infiltration (2% Lignocaine), right mediolateral episiotomy given.\n' +
      'Delivered a live mature baby spontaneously; cried immediately at birth, cord clamped & handed to pediatrician.\n' +
      'Placenta delivered complete by controlled cord traction (AMTSL followed); uterus well contracted.\n' +
      'Cervix & fornices inspected intact; episiotomy wound sutured in layers with Vicryl 2-0.\n' +
      'Bladder catheterized, clear urine drained; no active bleeding P/V.',
  },
  {
    id: 'og-twin',
    category: 'Obstetrics & Gynaecology',
    name: 'Twin Delivery (Breech Twin A + Emergency LSCS Twin B)',
    procedureName: 'Twin Delivery (Assisted Breech Twin A + Emergency LSCS Twin B)',
    procedureDone: 'Assisted Breech Delivery of Twin A & Emergency LSCS of Twin B',
    incision: 'Episiotomy & Pfannenstiel incision',
    findings: 'Twin A delivered by Breech; Twin B delivered by emergency LSCS; live twin babies',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 450,
    procedureNote:
      'Active second stage: right mediolateral episiotomy given, delivered alive Twin A by assisted breech delivery.\n' +
      'Twin B failed to progress; under SA, abdomen opened in layers by Pfannenstiel incision.\n' +
      'LSCS performed to deliver alive Twin B; both babies cried at birth and handed to pediatrician.\n' +
      'Placenta delivered complete, AMTSL followed; uterine wound & episiotomy closed in layers with Vicryl.\n' +
      'Hemostasis secured; uterus well contracted; no undue bleeding P/V.',
  },
  {
    id: 'og-tah',
    category: 'Obstetrics & Gynaecology',
    name: 'Total Abdominal Hysterectomy (TAH-BSO)',
    procedureName: 'Total Abdominal Hysterectomy with Bilateral Salpingo-Oophorectomy',
    procedureDone: 'TAH with BSO through Low Midline Incision',
    incision: 'Low midline incision',
    findings: 'Bulky fibroid uterus, bilateral adnexa gross pathology, clear resection margins',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 200,
    procedureNote:
      'Under SA/GA, abdomen opened in layers via low midline incision.\n' +
      'Round, infundibulopelvic, and uterine pedicles clamped, divided, and doubly ligated with Vicryl 1.\n' +
      'Uterus with bilateral tubes & ovaries excised en-bloc and sent for HPE.\n' +
      'Vaginal vault angles secured and vault closed with continuous Vicryl 1; hemostasis confirmed.\n' +
      'Peritoneal wash given; abdomen closed in layers; Foley draining clear urine.',
  },

  // ─── General & Laparoscopic Surgery ─────────────────────────────────────────
  {
    id: 'gs-lap-appx',
    category: 'General & Laparoscopic Surgery',
    name: 'Laparoscopic Appendectomy',
    procedureName: 'Laparoscopic Appendectomy',
    procedureDone: 'Three-Port Laparoscopic Appendectomy',
    incision: '10mm umbilical & two 5mm working ports',
    findings: 'Inflamed swollen retrocecal appendix with fibrinous exudate, no perforation',
    anesthesiaType: 'General Anaesthesia (GA)',
    bloodLossMl: 20,
    procedureNote:
      'Under GA, pneumoperitoneum created at 12 mmHg; 10mm umbilical & two 5mm ports placed under vision.\n' +
      'Appendix identified in retrocecal position, inflamed with periappendicular adhesions; adhesions released.\n' +
      'Mesoappendix divided; appendicular base double-ligated with Endoloops and transected cleanly.\n' +
      'Specimen retrieved via Endobag; caecal base healthy, perfect hemostasis secured, pelvic toilet done.\n' +
      'Port sites desufflated under vision; 10mm fascial defect closed; skin closed with Ethilon.',
  },
  {
    id: 'gs-lap-chole',
    category: 'General & Laparoscopic Surgery',
    name: 'Laparoscopic Cholecystectomy',
    procedureName: 'Laparoscopic Cholecystectomy',
    procedureDone: 'Four-Port Laparoscopic Cholecystectomy',
    incision: '10mm umbilical, 10mm epigastric, two 5mm subcostal ports',
    findings: 'Thickened gall bladder with multiple gallstones, clear Calot triangle',
    anesthesiaType: 'General Anaesthesia (GA)',
    bloodLossMl: 30,
    procedureNote:
      'Under GA, 4-port laparoscopy done; gallbladder grasped and Calot\'s triangle dissected.\n' +
      'Critical View of Safety (CVS) achieved; cystic duct and cystic artery doubly clipped and divided.\n' +
      'Gallbladder dissected off liver bed using electrocautery; specimen retrieved in Endobag.\n' +
      'Liver bed irrigated, no bile leak or active bleeding noted; dry field confirmed.\n' +
      'Ports removed under direct vision; fascial sheath closed; skin closed sterilely.',
  },
  {
    id: 'gs-hernia',
    category: 'General & Laparoscopic Surgery',
    name: 'Inguinal Hernia Repair (Mesh Hernioplasty)',
    procedureName: 'Lichtenstein Tension-Free Mesh Hernioplasty',
    procedureDone: 'Right / Left Lichtenstein Inguinal Mesh Hernioplasty',
    incision: 'Inguinal oblique incision',
    findings: 'Indirect / direct hernia sac reduced viable, posterior canal wall defect reinforced',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 25,
    procedureNote:
      'Under SA, oblique inguinal incision made; external oblique aponeurosis opened in line of fibers.\n' +
      'Ilioinguinal nerve preserved; cord mobilized; indirect sac dissected, reduced, and transfixed at deep ring.\n' +
      '7.5 × 15 cm Polypropylene mesh placed over posterior wall, anchored to pubic tubercle & inguinal ligament with Prolene 2-0.\n' +
      'Hemostasis confirmed; external oblique aponeurosis and subcutaneous tissue closed with Vicryl.\n' +
      'Skin closed subcuticularly with Monocryl 3-0; sterile pressure dressing applied.',
  },
  {
    id: 'gs-cyst-lipoma',
    category: 'General & Laparoscopic Surgery',
    name: 'Excision of Sebaceous Cyst / Lipoma',
    procedureName: 'Excision of Swelling / Cyst / Lipoma',
    procedureDone: 'Complete Surgical Excision under Local Anaesthesia',
    incision: 'Linear incision over swelling',
    findings: 'Well-encapsulated benign lesion excised in toto without capsular rupture',
    anesthesiaType: 'Local Infiltration (2% Lignocaine)',
    bloodLossMl: 10,
    procedureNote:
      'Under local infiltration (2% Lignocaine), linear skin incision made directly over the swelling.\n' +
      'Swelling dissected meticulously all around from surrounding tissue without capsular breach.\n' +
      'Cyst / Lipoma excised in toto with punctum/capsule intact and preserved for HPE.\n' +
      'Bed cauterized; meticulous hemostasis secured; dead space obliterated with Vicryl 3-0.\n' +
      'Skin closed with interrupted Ethilon 3-0; sterile dry dressing applied.',
  },

  // ─── Urology & Endourology ──────────────────────────────────────────────────
  {
    id: 'uro-urs-icl',
    category: 'Urology',
    name: 'URS + Lithotripsy (ICL / Laser) + DJ Stenting',
    procedureName: 'Ureteroscopy with Intracorporeal Lithotripsy & DJ Stenting',
    procedureDone: 'Right / Left URS with Laser/Pneumatic Lithotripsy & DJ Stent Placement',
    incision: 'Natural orifice (Cystoscopic)',
    findings: 'Ureteric calculus fragmented completely, DJ stent positioned in situ',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 10,
    procedureNote:
      'Under SA, cystourethroscopy performed in lithotomy position; ureteric orifice cannulated with 0.038" guidewire.\n' +
      '6/7.5F rigid ureteroscope negotiated into ureter under C-arm guidance; calculus visualized.\n' +
      'Stone completely fragmented using pneumatic lithotripter / laser into fine dust; gravel flushed out.\n' +
      '5F/26cm Double J (DJ) stent placed under fluoroscopic guidance; coils confirmed in kidney and bladder.\n' +
      'Bladder emptied and catheterized with 16F Foley catheter; clear urine drained.',
  },
  {
    id: 'uro-stent-rem',
    category: 'Urology',
    name: 'Cystoscopy + DJ Stent Removal',
    procedureName: 'Cystoscopic DJ Stent Removal',
    procedureDone: 'Cystoscopic Stent Removal under Local Anaesthesia',
    incision: 'Transurethral (No incision)',
    findings: 'DJ stent extracted complete and intact, clear bladder mucosa',
    anesthesiaType: 'Local Anaesthesia (2% Lignocaine Gel)',
    hasStent: false,
    bloodLossMl: 5,
    procedureNote:
      'Under local / short IV sedation, patient in lithotomy position; 19F cystoscope introduced per urethra.\n' +
      'Bladder mucosa inspected normal; lower coil of in-situ DJ stent identified at ureteric orifice.\n' +
      'Stent grasped securely with rigid foreign-body forceps and extracted gently under vision.\n' +
      'Complete, intact stent verified post-extraction; no mucosal injury or bleeding noted.\n' +
      'Bladder emptied; patient voided clear urine comfortably before discharge.',
  },
  {
    id: 'uro-turp',
    category: 'Urology',
    name: 'Transurethral Resection of Prostate (TURP)',
    procedureName: 'Transurethral Resection of Prostate (TURP)',
    procedureDone: 'Monopolar / Bipolar TURP under Continuous Glycine Irrigation',
    incision: 'Transurethral (No incision)',
    findings: 'Trilobar prostatic enlargement resected to surgical capsule, verumontanum preserved',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 100,
    procedureNote:
      'Under SA, 24F Iglesias resectoscope introduced under 1.5% Glycine irrigation in lithotomy position.\n' +
      'Bilateral lateral & median prostatic lobes resected systematically down to surgical capsule; verumontanum preserved.\n' +
      'Chips evacuated completely with Ellik evacuator and sent for HPE; prostatic bed thoroughly coagulated.\n' +
      '22F 3-way silicone Foley catheter placed, bulb inflated to 30ml, connected to continuous NS irrigation.\n' +
      'Post-op irrigation running clear with no active bleeding.',
  },

  // ─── Orthopaedics & Trauma ──────────────────────────────────────────────────
  {
    id: 'ortho-orif',
    category: 'Orthopaedics',
    name: 'Fracture ORIF with Plate & Screws (LCP)',
    procedureName: 'Open Reduction & Internal Fixation with Locking Plate',
    procedureDone: 'ORIF with Contoured Locking Compression Plate & Screws',
    incision: 'Standard open surgical approach over fracture',
    findings: 'Displaced fracture reduced anatomically with rigid plate-and-screw construct',
    anesthesiaType: 'Spinal / Regional Plexus Block',
    bloodLossMl: 50,
    procedureNote:
      'Under regional/spinal block with tourniquet control, fracture site exposed via standard approach.\n' +
      'Hematoma evacuated, fracture ends cleaned and anatomically reduced under direct vision.\n' +
      'Contoured Locking Compression Plate (LCP) applied across fracture, fixed with locking & cortical screws.\n' +
      'Rigid anatomical fixation confirmed clinically and radiographically under C-arm fluoroscopy.\n' +
      'Wound washed, hemostasis secured; closed in layers over suction drain; sterile POP slab applied.',
  },
  {
    id: 'ortho-kwire',
    category: 'Orthopaedics',
    name: 'Closed Reduction & Percutaneous K-Wire Fixation',
    procedureName: 'Closed Reduction & Percutaneous K-Wire Fixation',
    procedureDone: 'Closed Reduction with Percutaneous K-Wire Fixation under C-Arm',
    incision: 'Percutaneous pin sites (No incision)',
    findings: 'Fracture alignment and stability achieved under fluoroscopic control',
    anesthesiaType: 'Regional Block / IV Sedation',
    bloodLossMl: 10,
    procedureNote:
      'Under sedation / block, limb prepped sterilely; fracture reduced by closed manipulation.\n' +
      'Anatomical alignment verified under continuous C-arm fluoroscopic control in AP and lateral views.\n' +
      'Two/three 1.5–2.0 mm smooth Kirschner wires (K-wires) driven percutaneously across fracture site.\n' +
      'Stable rigid fixation confirmed on stress views under C-arm; K-wire ends bent and cut outside skin.\n' +
      'Sterile pin-site dressing applied with well-padded protective POP slab; distal pulses checked intact.',
  },
];

