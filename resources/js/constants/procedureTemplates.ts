export interface ProcedureTemplate {
  id: string;
  category: 'Obstetrics & Gynaecology' | 'Urology & Endourology' | 'General & Laparoscopic Surgery' | 'Orthopaedics';
  name: string;
  procedureName: string;
  procedureDone: string;
  incision: string;
  findings: string;
  anesthesiaType?: string;
  hasStent?: boolean;
  bloodLossMl?: number;
  icdCode?: string;
  procedureNote: string;
}

export const PROCEDURE_TEMPLATES: ProcedureTemplate[] = [
  // ═══════════════════════════════════════════════════════════════════════════
  // 1. OBSTETRICS & GYNAECOLOGY
  // ═══════════════════════════════════════════════════════════════════════════
  {
    id: 'og-ftnd',
    category: 'Obstetrics & Gynaecology',
    name: 'Normal Vaginal Delivery with Episiotomy (FTND)',
    procedureName: 'Labour Natural with Episiotomy',
    procedureDone: 'Spontaneous Vaginal Delivery with Right Mediolateral Episiotomy',
    incision: 'Right mediolateral episiotomy',
    findings: 'Single live mature baby delivered spontaneously, placenta & membranes complete, uterus well contracted',
    anesthesiaType: 'Local Infiltration (2% Lignocaine)',
    bloodLossMl: 150,
    icdCode: 'O80',
    procedureNote:
      'Labour natural with episiotomy with good uterine contraction, full cervix dilated.\n' +
      'After putting episiotomy, patient delivered alive baby; cried soon after birth, handed over to pediatrician.\n' +
      'Placenta & membranes delivered complete; AMTSL followed.\n' +
      'Uterus firm & contracted well; episiotomy wound closed in layers with Vicryl.\n' +
      'No undue bleeding P/V.',
  },
  {
    id: 'og-lscs',
    category: 'Obstetrics & Gynaecology',
    name: 'Emergency / Elective Repeat LSCS',
    procedureName: 'Lower Segment Caesarean Section (LSCS)',
    procedureDone: 'Emergency / Elective Repeat LSCS with Pfannenstiel Incision',
    incision: 'Suprapubic Transverse (Pfannenstiel) incision',
    findings: 'Thinned lower uterine segment, previous scar healthy, alive baby delivered, placenta complete',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 350,
    icdCode: 'O82',
    procedureNote:
      'Under SA, abdomen opened in layers by SPT incision.\n' +
      'LSCS done to deliver alive baby, cried soon after birth; baby handed over to pediatrician; AMTSL followed.\n' +
      'Uterine wound closed in 2 layers with Vicryl 1.\n' +
      'After securing perfect hemostasis, abdomen closed in layers.\n' +
      'Uterus contracted well; no undue bleeding P/V; no intraoperative complications.',
  },
  {
    id: 'og-lscs-pph',
    category: 'Obstetrics & Gynaecology',
    name: 'LSCS with Mild Atonic PPH Management',
    procedureName: 'Emergency LSCS with PPH Management',
    procedureDone: 'Emergency LSCS with Medical Management of PPH & Uterine Tamponade',
    incision: 'Suprapubic Transverse (Pfannenstiel) incision',
    findings: 'Alive baby delivered, placenta complete, intermittently relaxing uterus managed medically',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 550,
    icdCode: 'O82 / O72.1',
    procedureNote:
      'Under SA, abdomen opened in layers by SPT incision; LSCS done to deliver alive baby (handed to pediatrician).\n' +
      'Placenta complete; AMTSL followed; uterine wound closed in 2 layers.\n' +
      'Uterus intermittently relaxing with bleeding P/V; medical management of PPH given & Foley tamponade kept.\n' +
      'Perfect hemostasis achieved; abdomen closed in layers.\n' +
      'Uterus firm and contracted; bleeding controlled; shifted stable.',
  },
  {
    id: 'og-twin',
    category: 'Obstetrics & Gynaecology',
    name: 'Twin Delivery (Breech Twin A + Emergency LSCS Twin B)',
    procedureName: 'Twin Delivery (Assisted Breech Twin A + Emergency LSCS Twin B)',
    procedureDone: 'Breech Delivery of Twin A & Emergency LSCS for Twin B',
    incision: 'Right mediolateral episiotomy & SPT incision',
    findings: 'Twin A delivered by Breech; Twin B delivered by emergency LSCS; live twin babies',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 450,
    icdCode: 'O84.2',
    procedureNote:
      'Labour natural with episiotomy: delivered alive Twin A male baby by breech; handed to pediatrician.\n' +
      'Twin B not progressing; under SA, abdomen opened in layers by SPT incision.\n' +
      'LSCS done to deliver alive Twin B male baby; cried soon after birth; handed to pediatrician; AMTSL followed.\n' +
      'Uterine wound closed in 2 layers; episiotomy wound closed in layers; perfect hemostasis secured.\n' +
      'Uterus firm & contracted well; no undue bleeding P/V.',
  },
  {
    id: 'og-mva',
    category: 'Obstetrics & Gynaecology',
    name: 'Manual Vacuum Aspiration (MVA) for Missed / Incomplete Abortion',
    procedureName: 'Manual Vacuum Aspiration (MVA)',
    procedureDone: 'MVA under IV Sedation / TIVA for Retained Products of Conception',
    incision: 'Transvaginal (No skin incision)',
    findings: 'Products of conception evacuated; uterine cavity empty, walls firm',
    anesthesiaType: 'Total Intravenous Anaesthesia (TIVA)',
    bloodLossMl: 50,
    icdCode: 'O02.1',
    procedureNote:
      'Under IV sedation / TIVA, parts painted and draped well in lithotomy position.\n' +
      'Cervix visualized and stabilized with vulsellum; manual vacuum aspiration (MVA) performed.\n' +
      'Products of conception (placenta with membranes) removed in toto.\n' +
      'Check curettage confirmed uterine cavity empty and contracted.\n' +
      'Specimen sent for HPE; no undue bleeding P/V.',
  },
  {
    id: 'og-mva-sterilisation',
    category: 'Obstetrics & Gynaecology',
    name: 'MVA with Laparoscopic Tubal Sterilisation',
    procedureName: 'MVA with Laparoscopic Sterilisation',
    procedureDone: 'MVA followed by Laparoscopic Bilateral Tubal Ligation',
    incision: 'Subumbilical laparoscopic port & transvaginal',
    findings: 'Uterine cavity empty; bilateral fallopian tubes healthy; bilateral tubal sterilisation completed',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 60,
    icdCode: 'O04.9 / Z30.2',
    procedureNote:
      'Under SA, patient in lithotomy position; MVA performed and products of conception removed; cavity empty.\n' +
      'Laparoscopic port introduced; pneumoperitoneum created at 12 mmHg.\n' +
      'Bilateral fallopian tubes identified; sterilisation done by coagulating and transecting 1 cm of both tubes.\n' +
      'Hemostasis verified at tubal stumps; port site desufflated and closed with 2-0 Ethilon.\n' +
      'No intraoperative complications.',
  },
  {
    id: 'og-myomectomy',
    category: 'Obstetrics & Gynaecology',
    name: 'Open Myomectomy (Fibroid Uterus)',
    procedureName: 'Open Abdominal Myomectomy',
    procedureDone: 'Myomectomy with Reconstruction of Posterior/Lateral Uterine Wall',
    incision: 'Suprapubic Transverse (Pfannenstiel) incision',
    findings: 'Intramural fibroid in posterior wall (8x9 cm) and subserosal fibroids excised; bilateral ovaries normal',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 250,
    icdCode: 'D25.9',
    procedureNote:
      'Under SA, abdomen opened in layers by SPT incision.\n' +
      'Vasopressin infiltration given over uterine capsule; myomectomy proceeded in usual way.\n' +
      'Intramural and subserosal fibroids enucleated cleanly and sent for HPE.\n' +
      'After securing perfect hemostasis, uterine bed & scar closed in layers with 2-0 Vicryl.\n' +
      'Abdomen closed in layers; no undue bleeding P/V; uneventful recovery.',
  },
  {
    id: 'og-tlh-cystectomy',
    category: 'Obstetrics & Gynaecology',
    name: 'TLH with Ovarian Cystectomy',
    procedureName: 'Total Laparoscopic Hysterectomy with Ovarian Cystectomy',
    procedureDone: 'TLH with Left / Right Ovarian Cystectomy',
    incision: '10mm umbilical & working laparoscopic ports',
    findings: 'Bulky uterus; ovarian simple cyst excised intact; vaginal vault closed; clear pelvis',
    anesthesiaType: 'General Anaesthesia (ETGA)',
    bloodLossMl: 120,
    icdCode: 'N83.20 / D25.9',
    procedureNote:
      'Under ETGA, patient in lithotomy position; laparoscopy ports introduced and pneumoperitoneum created.\n' +
      'Uterus bulky; ovarian cyst identified and cystectomy performed in usual way; specimen sent for biopsy.\n' +
      'Bilateral uterine pedicles and round ligaments coagulated and divided; uterus detached.\n' +
      'Vaginal vault secured and closed with 2-0 Ethilon / Catgut.\n' +
      'Perfect hemostasis confirmed; port sites closed; Foley draining clear urine.',
  },
  {
    id: 'og-vh-pfr',
    category: 'Obstetrics & Gynaecology',
    name: 'Vaginal Hysterectomy with Pelvic Floor Repair (VH with PFR)',
    procedureName: 'Vaginal Hysterectomy with Anterior & Posterior Colporrhaphy',
    procedureDone: 'VH with PFR and Cystocele Repair',
    incision: 'Inverted "T" incision on anterior vaginal wall & posterior mucosal incision',
    findings: 'Uterovaginal prolapse III° with cystocele; atrophic uterus; complete vaginal excision & repair',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 150,
    icdCode: 'N81.2 / N81.10',
    procedureNote:
      'Under SA, patient in lithotomy position; inverted "T" incision made in anterior vaginal wall.\n' +
      'Uterovesical fold opened, bladder mobilized; uterosacral and cardinal pedicles clamped, cut, and ligated.\n' +
      'VH done in usual way; vault closed with 2-0 chromic catgut.\n' +
      'PFR and cystocele repair completed; hemostasis secured; no undue bleeding P/V.\n' +
      '16F Foley catheter placed with clear urine output; vaginal pack placed.',
  },
  {
    id: 'og-curettage-biopsy',
    category: 'Obstetrics & Gynaecology',
    name: 'Endometrial Curettage with Cervical Biopsy',
    procedureName: 'Endometrial Curettage with Cervix Biopsy',
    procedureDone: 'Diagnostic Curettage with Ectocervical Punch Biopsy',
    incision: 'Transvaginal (No incision)',
    findings: 'Moderate endometrial curettings obtained; ectocervical biopsy taken; sent for histopathology',
    anesthesiaType: 'Total Intravenous Anaesthesia (TIVA)',
    bloodLossMl: 20,
    icdCode: 'N93.8',
    procedureNote:
      'Under TIVA, parts painted and draped well in lithotomy position.\n' +
      'Cervix exposed with Sim\'s speculum; punch biopsy taken from ectocervix.\n' +
      'Endometrial curettage performed systematically; moderate curettings obtained.\n' +
      'All specimens labeled and sent for histopathological examination.\n' +
      'Hemostasis verified; no undue bleeding P/V.',
  },
  {
    id: 'og-expulsion-curettage',
    category: 'Obstetrics & Gynaecology',
    name: 'Spontaneous Expulsion with Check Curettage (IUD / Anomaly)',
    procedureName: 'Spontaneous Expulsion with Check Curettage',
    procedureDone: 'Medical Expulsion of Non-Viable Conception with Check Curettage',
    incision: 'Transvaginal (No incision)',
    findings: 'Spontaneous complete expulsion of fetus and placenta; uterine cavity empty on check curettage',
    anesthesiaType: 'IV Sedation / Analgesia',
    bloodLossMl: 100,
    icdCode: 'O35.9 / O03.4',
    procedureNote:
      'Spontaneous expulsion of fetus with placenta and membranes complete.\n' +
      'Inj. Syntocinon 10 IU IM administered.\n' +
      'Under IV sedation, check curettage performed.\n' +
      'Uterine cavity found to be completely empty and well contracted.\n' +
      'Hemostasis secured; no undue bleeding P/V.',
  },

  // ═══════════════════════════════════════════════════════════════════════════
  // 2. UROLOGY & ENDOUROLOGY
  // ═══════════════════════════════════════════════════════════════════════════
  {
    id: 'uro-ursl-stenting',
    category: 'Urology & Endourology',
    name: 'URSL & DJ Stenting (Ureteric / VUJ Calculus)',
    procedureName: 'Ureteroscopy with Laser/ICL Lithotripsy & DJ Stenting',
    procedureDone: 'Right / Left URSL with ICL & 5F/26cm DJ Stent Placement',
    incision: 'Transurethral (No incision)',
    findings: 'Ureteric / VUJ calculus fragmented completely; all bits removed; DJ stent in situ',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 10,
    icdCode: 'N20.1',
    procedureNote:
      'Under SA, patient in lithotomy position; parts painted and draped well.\n' +
      'Urethrocystoscopy done with ureteroscope; normal bladder; ureteric orifice cannulated with guidewire.\n' +
      'Ureteroscopy performed under C-arm guidance; calculus fragmented completely using laser / ICL.\n' +
      'All stone bits evacuated; 5F/26cm DJ stent placed under fluoroscopic confirmation.\n' +
      'Bladder catheterized with 16F Foley catheter; clear urine drained.',
  },
  {
    id: 'uro-rirs-stenting',
    category: 'Urology & Endourology',
    name: 'RIRS & DJ Stenting (PUJ / Renal Calculus)',
    procedureName: 'Retrograde Intrarenal Surgery (RIRS) with DJ Stenting',
    procedureDone: 'Right / Left / Bilateral RIRS with Holmium Laser & DJ Stenting',
    incision: 'Transurethral (No incision)',
    findings: 'PUJ / Renal calculus located, pulverized by laser into fine dust; DJ stent placed under C-arm',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 15,
    icdCode: 'N20.0',
    procedureNote:
      'Under SA, lithotomy position; cystourethroscopy performed and ureteric orifice cannulated under C-arm.\n' +
      '8/9.8F URS passed; guidewire positioned; 9/11F ureteral access sheath placed.\n' +
      'Flexible ureteroscope introduced; Holmium laser intracorporeal lithotripsy (ICL) performed.\n' +
      'Calculus completely fragmented into dust; 5F/24cm (or 26cm) DJ stent placed under fluoroscopy.\n' +
      '16F Foley catheter placed; clear urine draining; no complications.',
  },
  {
    id: 'uro-cystolitho-rirs',
    category: 'Urology & Endourology',
    name: 'Cystolitholapaxy with RIRS & DJ Stenting',
    procedureName: 'Cystolitholapaxy with RIRS & DJ Stenting',
    procedureDone: 'Transurethral Cystolithotripsy with Flexible Ureteroscopy & DJ Stenting',
    incision: 'Transurethral (No incision)',
    findings: 'Bladder calculi crushed and evacuated; renal calculus dusted via flexible URS; DJ stent in situ',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 25,
    icdCode: 'N21.0 / N20.0',
    procedureNote:
      'Under SA, lithotomy position; bladder calculi visualized and crushed with stone punch / resectoscope; bits evacuated.\n' +
      'Urethrocystoscopy performed; ureteric orifice cannulated with guidewire under C-arm guidance.\n' +
      '9/11F access sheath placed; flexible ureteroscope + Holmium laser lithotripsy performed on renal calculus.\n' +
      'Stone fragmented completely; 5F/26cm DJ stent positioned across ureter under C-arm.\n' +
      '16F Foley catheter placed; clear urine output confirmed.',
  },
  {
    id: 'uro-stent-placement',
    category: 'Urology & Endourology',
    name: 'Standalone DJ Stenting (Pre-RIRS Drainage)',
    procedureName: 'Cystoscopic DJ Stent Placement',
    procedureDone: 'Right / Left DJ Stenting under C-Arm Fluoroscopy',
    incision: 'Transurethral (No incision)',
    findings: 'Ureter cannulated past obstructing calculus; 5F/26cm DJ stent placed in renal pelvis; later RIRS planned',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 5,
    icdCode: 'N20.0 / N13.2',
    procedureNote:
      'Under SA, urethrocystoscopy performed with ureteroscope in lithotomy position.\n' +
      'Normal bladder mucosa; ureteric orifice cannulated with guidewire under C-arm guidance.\n' +
      'Ureteroscopy done past calculus; 5F/26cm DJ stent placed with coils confirmed in kidney & bladder.\n' +
      'Bladder catheterized with 16F Foley catheter; clear urine drained.\n' +
      'Elective RIRS planned after 2–3 weeks.',
  },
  {
    id: 'uro-stent-removal',
    category: 'Urology & Endourology',
    name: 'Cystoscopic DJ Stent Removal',
    procedureName: 'Cystoscopic DJ Stent Removal',
    procedureDone: 'Right / Left DJ Stent Removal under Local Anaesthesia',
    incision: 'Transurethral (No incision)',
    findings: 'In-situ DJ stent grasped at bladder coil and extracted intact; clear bladder mucosa',
    anesthesiaType: 'Local Anaesthesia (2% Lignocaine Gel)',
    hasStent: false,
    bloodLossMl: 5,
    icdCode: 'Z46.6',
    procedureNote:
      'Under LA / sedation, patient placed in lithotomy position; parts painted and draped well.\n' +
      'Cystourethroscopy done with 19F sheath; normal bladder mucosa visualized.\n' +
      'In-situ DJ stent identified at ureteric orifice and grasped with foreign body forceps.\n' +
      'Stent removed gently and verified complete and intact.\n' +
      'No intraoperative complications; clear urine output confirmed.',
  },
  {
    id: 'uro-pcnl',
    category: 'Urology & Endourology',
    name: 'Percutaneous Nephrolithotomy (PCNL)',
    procedureName: 'Percutaneous Nephrolithotomy with DJ Stenting',
    procedureDone: 'Right / Left PCNL with Antegrade DJ Stenting',
    incision: '1 cm flank puncture incision',
    findings: 'Renal calculus fragmented and evacuated via nephroscope; antegrade DJ stent in situ',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 80,
    icdCode: 'N20.0',
    procedureNote:
      'Under SA, lithotomy position: cystoscopy done, ureteric catheter passed under C-arm; bladder catheterized.\n' +
      'Patient positioned prone/supine; posterior calyx punctured under C-arm fluoroscopy with contrast.\n' +
      'Tract dilated; 28F Amplatz sheath inserted; nephroscope introduced.\n' +
      'Stone fragmented and all bits removed cleanly; 5F/26cm DJ stent placed antegrade.\n' +
      'Nephrostomy / flank puncture wound closed; patient stable.',
  },
  {
    id: 'uro-turp',
    category: 'Urology & Endourology',
    name: 'Bipolar / Monopolar TURP (BPH)',
    procedureName: 'Transurethral Resection of the Prostate (TURP)',
    procedureDone: 'Bipolar TURP for Benign Prostatic Hyperplasia',
    incision: 'Transurethral (No incision)',
    findings: 'Grade III trilobar enlargement resected down to surgical capsule; verumontanum preserved; bits evacuated',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 100,
    icdCode: 'N40.1',
    procedureNote:
      'Under SA, lithotomy position; 20F cystoscopy confirmed Grade III trilobar prostatic enlargement.\n' +
      'Using 24F resectoscope with continuous irrigation, bipolar TURP performed.\n' +
      'Prostatic tissue resected systematically down to surgical capsule; verumontanum preserved.\n' +
      'Prostate chips evacuated completely with Ellik evacuator; specimen sent for HPE.\n' +
      '22F 3-way silicone Foley catheter placed with continuous irrigation running clear.',
  },
  {
    id: 'uro-oiu',
    category: 'Urology & Endourology',
    name: 'Optical Internal Urethrotomy (OIU for Stricture Urethra)',
    procedureName: 'Optical Internal Urethrotomy (OIU)',
    procedureDone: 'Cold Knife Urethrotomy at 12 O\'Clock Position',
    incision: 'Transurethral (No incision)',
    findings: 'Anterior/bulbar urethral stricture incised; wide lumen achieved; catheter passed smoothly',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 20,
    icdCode: 'N35.9',
    procedureNote:
      'Under SA, lithotomy position; urethroscopy performed; narrowed stricture segment identified.\n' +
      'Hydrophilic guidewire negotiated across the stricture into the bladder under vision.\n' +
      'Cold knife optical internal urethrotomy performed at 12 o\'clock position until full caliber restored.\n' +
      '16F silicone Foley catheter placed across the incised area into bladder.\n' +
      'Clear urine drained; no active bleeding.',
  },
  {
    id: 'uro-clot-evac',
    category: 'Urology & Endourology',
    name: 'Cystoscopy + Bladder Clot Evacuation',
    procedureName: 'Cystoscopy with Bladder Clot Evacuation',
    procedureDone: 'Cystoscopic Evacuation of Intravesical Clots & Hemostasis',
    incision: 'Transurethral (No incision)',
    findings: 'Intravesical organized hematoma evacuated; inflamed bladder mucosa; clear irrigation return',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 50,
    icdCode: 'R31.9',
    procedureNote:
      'Under SA, lithotomy position; 20F cystourethroscopy performed.\n' +
      'Bladder inspected: inflamed mucosa with intravesical organized blood clots visualized.\n' +
      'Complete clot evacuation performed using Toomey syringe / resectoscope sheath.\n' +
      'Bleeding points fulgurated; bladder mucosa clear.\n' +
      '16F 3-way Foley catheter placed on continuous bladder irrigation.',
  },
  {
    id: 'uro-nephrectomy',
    category: 'Urology & Endourology',
    name: 'Laparoscopic / Open Right Nephrectomy (NFK)',
    procedureName: 'Right Nephrectomy for Non-Functioning Kidney',
    procedureDone: 'Laparoscopic / Open Right Nephrectomy for PUJO Non-Functioning Kidney',
    incision: 'Laparoscopic ports / Flank incision',
    findings: 'Grossly hydronephrotic non-functioning right kidney; renal vessels double-clipped; specimen removed',
    anesthesiaType: 'General Anaesthesia (ETGA)',
    bloodLossMl: 200,
    icdCode: 'N28.9',
    procedureNote:
      'Under ETGA, patient in left lateral position; laparoscopy ports placed and white line of Toldt mobilized.\n' +
      'Grossly hydronephrotic non-functioning kidney identified.\n' +
      'Renal hilum dissected using two-window technique; renal artery & vein double clipped / ligated.\n' +
      'Ureter divided; non-functioning kidney mobilized and removed en-bloc; specimen sent for HPE.\n' +
      'Hemostasis secured; abdominal drain placed; wound closed in layers.',
  },
  {
    id: 'uro-vh-pfr-ursl',
    category: 'Urology & Endourology',
    name: 'Combined VH + PFR + Left URSL & Stenting',
    procedureName: 'VH with PFR and Left URSL',
    procedureDone: 'Vaginal Hysterectomy with PFR and Left Ureteroscopy with DJ Stenting',
    incision: 'Inverted "T" vaginal incision & transurethral',
    findings: 'UV prolapse with cystocele repaired; left ureteric calculus fragmented; DJ stent in situ',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    hasStent: true,
    bloodLossMl: 160,
    icdCode: 'N81.2 / N20.1',
    procedureNote:
      'Under SA, lithotomy position; inverted "T" incision made; VH with PFR done in usual way; vault closed.\n' +
      'Urethrocystoscopy done; normal bladder; left ureteric orifice cannulated with guidewire.\n' +
      'Left ureteroscopy done; calculus visualized, fragmented completely; bits removed.\n' +
      '5F/26cm DJ stent placed under C-arm; 16F Foley catheter placed; clear urine output.\n' +
      'No intraoperative complications.',
  },

  // ═══════════════════════════════════════════════════════════════════════════
  // 3. GENERAL & LAPAROSCOPIC SURGERY
  // ═══════════════════════════════════════════════════════════════════════════
  {
    id: 'gs-lap-chole',
    category: 'General & Laparoscopic Surgery',
    name: 'Laparoscopic Cholecystectomy',
    procedureName: 'Laparoscopic Cholecystectomy',
    procedureDone: 'Four-Port Laparoscopic Cholecystectomy',
    incision: '10mm umbilical, 10mm epigastric, two 5mm working ports',
    findings: 'Thickened gall bladder with multiple calculi, cystic duct & artery clipped, liver bed dry',
    anesthesiaType: 'General Anaesthesia (ETGA)',
    bloodLossMl: 30,
    icdCode: 'K80.00',
    procedureNote:
      'Under ETGA, supine position; laparoscopy ports placed and pneumoperitoneum created.\n' +
      'Calot\'s triangle dissected; cystic artery double-clipped; cystic duct clipped and cut.\n' +
      'Gallbladder dissected off liver bed with electrocautery; specimen removed with stones.\n' +
      'Saline lavage given; perfect hemostasis secured; abdominal drain tube (DT) placed.\n' +
      'Ports closed; skin closed with 2-0 Ethilon; no complications.',
  },
  {
    id: 'gs-lap-appx',
    category: 'General & Laparoscopic Surgery',
    name: 'Laparoscopic Appendectomy',
    procedureName: 'Laparoscopic Appendectomy',
    procedureDone: 'Three-Port Laparoscopic Appendectomy',
    incision: '10mm supraumbilical & two 5mm working ports',
    findings: 'Appendix inflamed / perforated with fecolith, appendectomy done, DT placed',
    anesthesiaType: 'General Anaesthesia (GA)',
    bloodLossMl: 25,
    icdCode: 'K35.80',
    procedureNote:
      'Under GA, supine position; 10mm supraumbilical and two 5mm working ports placed.\n' +
      'Pneumoperitoneum created; appendix identified (inflamed / perforated with fecolith).\n' +
      'Mesoappendix divided; appendicular base ligated and appendectomy completed.\n' +
      'Pelvic wash given; perfect hemostasis achieved; abdominal drain placed.\n' +
      'Port sites closed in layers; no intraoperative complications.',
  },
  {
    id: 'gs-hernia',
    category: 'General & Laparoscopic Surgery',
    name: 'Inguinal Hernia Mesh Repair (Lichtenstein)',
    procedureName: 'Lichtenstein Tension-Free Mesh Hernioplasty',
    procedureDone: 'Right / Left Lichtenstein Inguinal Mesh Hernioplasty',
    incision: 'Inguinal oblique incision',
    findings: 'Indirect / direct hernia sac reduced viable, posterior canal wall reinforced with mesh',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 25,
    icdCode: 'K40.90',
    procedureNote:
      'Under SA, oblique inguinal incision made; external oblique aponeurosis opened in line of fibers.\n' +
      'Cord mobilized; hernia sac dissected, reduced into peritoneal cavity, and neck transfixed.\n' +
      '7.5 × 15 cm Polypropylene mesh anchored to pubic tubercle & inguinal ligament with Prolene 2-0.\n' +
      'Hemostasis confirmed; external oblique and subcutaneous tissue closed with Vicryl.\n' +
      'Skin closed subcuticularly with Monocryl 3-0; sterile pressure dressing applied.',
  },
  {
    id: 'gs-fistulectomy',
    category: 'General & Laparoscopic Surgery',
    name: 'Fistulectomy (Fistula in Ano)',
    procedureName: 'Fistulectomy',
    procedureDone: 'Complete Excision of Fistula Tract in Ano',
    incision: 'Perianal elliptical incision over fistula tract',
    findings: 'Fistula tract identified with probe, excised completely, healthy granulation tissue base',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 20,
    icdCode: 'K60.3',
    procedureNote:
      'Under SA, patient placed in lithotomy position; perianal parts painted and draped.\n' +
      'Fistula probe negotiated through external opening to internal anal crypt opening.\n' +
      'Fistula tract excised in toto (fistulectomy done); tract curetted cleanly.\n' +
      'Hemostasis secured with electrocautery; wound kept open for secondary intention healing.\n' +
      'Anal pack placed with sterile dressing.',
  },
  {
    id: 'gs-fissurectomy-lis',
    category: 'General & Laparoscopic Surgery',
    name: 'Fissurectomy with LIS (Fissure in Ano)',
    procedureName: 'Fissurectomy with Lateral Internal Sphincterotomy',
    procedureDone: 'Fissurectomy with Closed / Open Lateral Sphincterotomy',
    incision: 'Perianal at 6 o\'clock & 3 o\'clock position',
    findings: 'Chronic indurated fissure in ano at 6 o\'clock position, hypertrophied anal papilla excised',
    anesthesiaType: 'Spinal Anaesthesia (SA)',
    bloodLossMl: 15,
    icdCode: 'K60.2',
    procedureNote:
      'Under SA, patient in lithotomy position; perianal parts prepped and draped sterilely.\n' +
      'Chronic fissure in ano identified at 6 o\'clock position with sentinel tag.\n' +
      'Fissurectomy performed with excision of indurated edges and sentinel skin tag.\n' +
      'Partial lateral internal sphincterotomy (LIS) completed at 3 o\'clock position.\n' +
      'Hemostasis secured; antiseptic ointment and anal pack applied.',
  },
  {
    id: 'gs-circumcision',
    category: 'General & Laparoscopic Surgery',
    name: 'Circumcision (Phimosis)',
    procedureName: 'Circumcision',
    procedureDone: 'Sleeve / Guillotine Circumcision under Aseptic Precautions',
    incision: 'Circumferential preputial incision',
    findings: 'Tight non-retractile prepuce / phimosis excised cleanly, glans penis healthy',
    anesthesiaType: 'Spinal Anaesthesia / Penile Block',
    bloodLossMl: 10,
    icdCode: 'N47.1',
    procedureNote:
      'Under SA / Penile block with ASP, patient in supine position.\n' +
      'Prepuce retracted, smegma cleaned; dorsal and ventral slit made.\n' +
      'Circumcision done in usual way; redundant inner and outer prepuce excised.\n' +
      'Frenular and circular bleeding points coagulated; skin approximated with 3-0 Chromic Catgut.\n' +
      'Sterile paraffin dressing applied; no complications.',
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
    icdCode: 'L72.3 / D17.9',
    procedureNote:
      'Under local infiltration (2% Lignocaine), linear skin incision made directly over the swelling.\n' +
      'Swelling dissected meticulously all around from surrounding tissue without capsular breach.\n' +
      'Cyst / Lipoma excised in toto with punctum/capsule intact and preserved for HPE.\n' +
      'Bed cauterized; meticulous hemostasis secured; dead space obliterated with Vicryl 3-0.\n' +
      'Skin closed with interrupted Ethilon 3-0; sterile dry dressing applied.',
  },

  // ═══════════════════════════════════════════════════════════════════════════
  // 4. ORTHOPAEDICS & TRAUMA
  // ═══════════════════════════════════════════════════════════════════════════
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
    icdCode: 'S82.891A',
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
    icdCode: 'S52.509A',
    procedureNote:
      'Under sedation / block, limb prepped sterilely; fracture reduced by closed manipulation.\n' +
      'Anatomical alignment verified under continuous C-arm fluoroscopic control in AP and lateral views.\n' +
      'Two/three 1.5–2.0 mm smooth Kirschner wires (K-wires) driven percutaneously across fracture site.\n' +
      'Stable rigid fixation confirmed on stress views under C-arm; K-wire ends bent and cut outside skin.\n' +
      'Sterile pin-site dressing applied with well-padded protective POP slab; distal pulses checked intact.',
  },
];
