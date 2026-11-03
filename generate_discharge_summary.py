import os
import sys
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import mm
from reportlab.platypus import (
    BaseDocTemplate, PageTemplate, Frame, Paragraph, Spacer, Table, TableStyle, PageBreak, NextPageTemplate
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT, TA_JUSTIFY

def build_pdf(filename="Guru_Hospital_Discharge_Summary_Anitha_IP2026-567.pdf", long_address=False):
    # Page setup
    # A4 is 210 x 297 mm = 595.276 x 841.890 pt
    page_w, page_h = A4
    margin_lr = 16 * mm # 45.354 pt
    margin_bottom = 18 * mm # 51.024 pt
    printable_w = page_w - 2 * margin_lr # 178 mm = 504.567 pt

    # Page 1: Top 45 mm completely blank
    # Top of page = 841.89 pt. 45 mm = 127.559 pt.
    # Top of Frame 1 = 841.89 - 127.559 = 714.331 pt.
    # Bottom of Frame 1 = 18 mm = 51.024 pt.
    frame1_h = page_h - 45 * mm - margin_bottom # 234 mm = 663.307 pt
    frame1 = Frame(
        margin_lr, margin_bottom, printable_w, frame1_h,
        id='frame1',
        leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0
    )

    # Page 2: Running header at top, running footer at bottom
    # Header baseline at y = 812 pt, rule at y = 806 pt.
    # Content top at y = 798 pt.
    # Footer rule at y = 62 pt, text at y = 48 pt.
    # Content bottom at y = 68 pt.
    frame2_bottom = 68
    frame2_top = 798
    frame2_h = frame2_top - frame2_bottom # 730 pt = 257.5 mm
    frame2 = Frame(
        margin_lr, frame2_bottom, printable_w, frame2_h,
        id='frame2',
        leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0
    )

    def on_page1(canvas, doc):
        # Page 1: leave top 45mm completely blank. No header, no footer, no page number.
        pass

    def on_page2(canvas, doc):
        canvas.saveState()
        # Running Header
        header_y = 812
        rule_y = 806
        canvas.setFont("Helvetica-Bold", 8.5)
        canvas.setFillColor(colors.HexColor("#111111"))
        canvas.drawString(margin_lr, header_y, "DISCHARGE SUMMARY")
        
        canvas.setFont("Helvetica", 8.5)
        canvas.setFillColor(colors.HexColor("#444444"))
        right_text = "Mrs. Anitha  |  IP No. 2026/567  |  UHID 2023/3931"
        canvas.drawRightString(page_w - margin_lr, header_y, right_text)
        
        canvas.setStrokeColor(colors.HexColor("#94a3b8"))
        canvas.setLineWidth(0.5)
        canvas.line(margin_lr, rule_y, page_w - margin_lr, rule_y)

        # Running Footer
        footer_rule_y = 62
        footer_text_y = 48
        canvas.line(margin_lr, footer_rule_y, page_w - margin_lr, footer_rule_y)
        
        canvas.setFont("Helvetica", 8)
        canvas.setFillColor(colors.HexColor("#555555"))
        canvas.drawString(margin_lr, footer_text_y, "Guru Hospital - Discharge Summary (IP: 2026/567) - Mrs. Anitha")
        canvas.drawRightString(page_w - margin_lr, footer_text_y, "Page 2 of 2")
        canvas.restoreState()

    doc = BaseDocTemplate(
        filename,
        pagesize=A4,
        leftMargin=margin_lr,
        rightMargin=margin_lr,
        topMargin=45 * mm,
        bottomMargin=margin_bottom
    )
    doc.addPageTemplates([
        PageTemplate(id='Page1', frames=[frame1], onPage=on_page1),
        PageTemplate(id='Page2', frames=[frame2], onPage=on_page2)
    ])

    styles = getSampleStyleSheet()

    # Custom styles
    style_title = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=18,
        alignment=TA_CENTER,
        textColor=colors.HexColor('#111111'),
        spaceAfter=8
    )

    style_sec_heading = ParagraphStyle(
        'SecHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9.5,
        leading=12,
        textColor=colors.HexColor('#0f172a'),
        textTransform='uppercase'
    )

    style_body = ParagraphStyle(
        'Body',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=12.5,
        textColor=colors.HexColor('#111111')
    )

    style_body_bold = ParagraphStyle(
        'BodyBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=12.5,
        textColor=colors.HexColor('#111111')
    )

    style_lbl = ParagraphStyle(
        'CellLabel',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7,
        leading=8.5,
        textColor=colors.HexColor('#475569')
    )

    style_val = ParagraphStyle(
        'CellValue',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=11.5,
        textColor=colors.HexColor('#000000')
    )

    style_val_regular = ParagraphStyle(
        'CellValueRegular',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#111111')
    )

    style_th = ParagraphStyle(
        'TableHead',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=10.5,
        textColor=colors.white
    )

    style_td = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#111111')
    )

    style_td_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#111111')
    )

    def section_heading(title, space_before=7, space_after=3):
        p = Paragraph(f"<b>{title.upper()}</b>", style_sec_heading)
        t = Table([[p]], colWidths=[printable_w])
        t.setStyle(TableStyle([
            ('PADDING', (0, 0), (-1, -1), 0),
            ('TOPPADDING', (0, 0), (-1, -1), space_before),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 2),
            ('LINEBELOW', (0, 0), (-1, -1), 0.75, colors.HexColor('#111111')),
        ]))
        return t

    def cell_box(lbl, val, bold_val=True):
        p_lbl = Paragraph(f"<b>{lbl.upper()}</b>", style_lbl)
        p_val = Paragraph(f"<b>{val}</b>" if bold_val else val, style_val if bold_val else style_val_regular)
        return [p_lbl, p_val]

    story = []

    # =========================================================================
    # PAGE 1
    # =========================================================================

    # 1. Centred title "DISCHARGE SUMMARY" (15 pt bold)
    story.append(Paragraph("DISCHARGE SUMMARY", style_title))

    # 2. Patient info grid
    # Name, Age/Sex, IP No., UHID (4 cols)
    # Phone, Admission, Discharge (3 cols)
    # Address (full-width row)
    address_val = "W/O Sarath, Veerasanur"
    if long_address:
        address_val = "W/O Sarath Veerasamur, Door No 4/112-B, Mariamman Kovil Street, Near Old Water Tank, Veerasamur Village, Pennagaram Taluk, Dharmapuri District, Tamil Nadu - 636810, India"

    row1 = [
        cell_box("Patient Name", "Mrs. Anitha"),
        cell_box("Age / Sex", "23 / Female"),
        cell_box("IP No.", "2026/567"),
        cell_box("UHID No.", "2023/3931"),
    ]
    t_patient1 = Table([row1], colWidths=[50*mm, 38*mm, 45*mm, 45*mm])
    t_patient1.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))

    row2 = [
        cell_box("Phone", "7373667465"),
        cell_box("Admission", "26/08/2026, 02:45 AM"),
        cell_box("Discharge", "29/08/2026, 04:00 PM"),
    ]
    t_patient2 = Table([row2], colWidths=[50*mm, 64*mm, 64*mm])
    t_patient2.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))

    row3 = [
        cell_box("Address", address_val),
    ]
    t_patient3 = Table([row3], colWidths=[printable_w])
    t_patient3.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))

    # Wrap patient info grid into a combined table structure or stacked
    t_patient_all = Table([[t_patient1], [t_patient2], [t_patient3]], colWidths=[printable_w])
    t_patient_all.setStyle(TableStyle([
        ('PADDING', (0,0), (-1,-1), 0),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2),
    ]))
    story.append(t_patient_all)
    story.append(Spacer(1, 3))

    # 3. Doctors grid: Consultant, Department, Date of surgery (Row 1); Surgeon, Anaesthetist, Assisted by (Row 2)
    doc_row1 = [
        cell_box("Consultant Doctor", "Dr. Ambika MS OG"),
        cell_box("Department", "Obstetrics & Gynaecology"),
        cell_box("Date of Surgery", "26/08/2026, 03:18 AM"),
    ]
    doc_row2 = [
        cell_box("Surgeon", "Dr. Ambika MS OG"),
        cell_box("Anaesthetist", "Dr. M.S. Karthick (SA)"),
        cell_box("Assisted By", "Staff Nurse Anitha"),
    ]
    t_doctors = Table([doc_row1, doc_row2], colWidths=[58*mm, 60*mm, 60*mm])
    t_doctors.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_doctors)
    story.append(Spacer(1, 4))

    # 4. Grey highlight box with bold left edge: Diagnosis & Surgery performed
    p_diag_lbl = Paragraph("<b>DIAGNOSIS</b>", style_lbl)
    p_diag_val = Paragraph("<b>G3P1L1A1 / TWINS / Preterm / PROM</b>", style_val)
    p_surg_lbl = Paragraph("<b>SURGERY PERFORMED</b>", style_lbl)
    p_surg_val = Paragraph("<b>Twin A: Labour natural with episiotomy  |  Twin B: Emergency LSCS</b>", style_val)

    diag_data = [
        [p_diag_lbl, p_diag_val],
        [p_surg_lbl, p_surg_val],
    ]
    t_diag = Table(diag_data, colWidths=[42*mm, 136*mm])
    t_diag.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#eeeeee')),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.black),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#cccccc')),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.black), # preserve bold edge
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(t_diag)
    story.append(Spacer(1, 4))

    # 5. Sections: Presenting complaints, Clinical examination, Procedure note
    story.append(section_heading("Presenting Complaints", space_before=4))
    story.append(Spacer(1, 2))
    story.append(Paragraph("G3P1L1A1 / TWINS / EDD 22-9-2026 / FH Good / admitted for safe confinement. C/O abdominal pain.", style_body))

    story.append(section_heading("Clinical Examination", space_before=5))
    story.append(Spacer(1, 2))
    exam_text = "<b>O/E:</b> A febrile, no pallor, no pedal edema. <b>CVS & RS:</b> NAD.<br/><b>P/A:</b> Uterus overdistended, not acting, both FH good."
    story.append(Paragraph(exam_text, style_body))

    story.append(section_heading("Procedure Note", space_before=5))
    story.append(Spacer(1, 2))
    proc_twin_a = "<b>Twin A:</b> Labour natural with episiotomy, with good uterine contraction and full cervical dilatation. After putting episiotomy, the patient delivered an alive Twin A male baby by breech. Baby handed over to pediatrician. Twin B baby did not progress to delivery; hence planned to proceed with C-section."
    proc_twin_b = "<b>Twin B:</b> Under SA, abdomen opened in layers by SPT incision. LSCS done to deliver an alive Twin B male baby, who cried soon after birth. Baby handed over to pediatrician. AMTSL followed. Uterine wound closed in 2 layers. After securing perfect hemostasis, abdomen closed in layers. Uterus firm and contracted well. Episiotomy wound closed in layers. No undue bleeding P/V."
    story.append(Paragraph(proc_twin_a, style_body))
    story.append(Spacer(1, 3))
    story.append(Paragraph(proc_twin_b, style_body))

    # 6. Baby/newborn details as two side-by-side cards
    story.append(section_heading("Baby / Newborn Details", space_before=5))
    story.append(Spacer(1, 3))

    def make_baby_card(title, sex, born, mode, weight, apgar, neonatal):
        rows = [
            [Paragraph(f"<b>{title}</b>", style_th), ""],
            [Paragraph("<b>SEX</b>", style_lbl), Paragraph(sex, style_val_regular)],
            [Paragraph("<b>BORN</b>", style_lbl), Paragraph(born, style_val_regular)],
            [Paragraph("<b>MODE</b>", style_lbl), Paragraph(mode, style_val_regular)],
            [Paragraph("<b>WEIGHT</b>", style_lbl), Paragraph(weight, style_val_regular)],
            [Paragraph("<b>APGAR</b>", style_lbl), Paragraph(apgar, style_val_regular)],
            [Paragraph("<b>NEONATAL<br/>CARE</b>", style_lbl), Paragraph(neonatal, style_val_regular)],
        ]
        card = Table(rows, colWidths=[24*mm, 63*mm])
        card.setStyle(TableStyle([
            ('SPAN', (0,0), (1,0)),
            ('BACKGROUND', (0,0), (1,0), colors.HexColor('#222222')),
            ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
            ('INNERGRID', (0,1), (-1,-1), 0.4, colors.HexColor('#e2e8f0')),
            ('TOPPADDING', (0,0), (-1,-1), 2),
            ('BOTTOMPADDING', (0,0), (-1,-1), 2),
            ('LEFTPADDING', (0,0), (-1,-1), 4),
            ('RIGHTPADDING', (0,0), (-1,-1), 4),
            ('VALIGN', (0,0), (-1,-1), 'TOP'),
            ('BACKGROUND', (0,1), (-1,-1), colors.HexColor('#ffffff')),
        ]))
        return card

    card_a = make_baby_card(
        "Newborn #1 - Twin A",
        "Alive male baby",
        "26.08.2026 at 03:18 AM",
        "Breech delivery with episiotomy",
        "2.25 kg",
        "7/10, 8/10",
        "RDS (+). Handed over to pediatrician; shifted to NICU."
    )
    card_b = make_baby_card(
        "Newborn #2 - Twin B",
        "Alive male baby",
        "26.08.2026 at 03:44 AM",
        "Emergency LSCS under SA",
        "2.25 kg",
        "8/10, 9/10",
        "Cried soon after birth. Handed over to pediatrician; AMTSL followed."
    )

    t_baby_cards = Table([[card_a, "", card_b]], colWidths=[87*mm, 4*mm, 87*mm])
    t_baby_cards.setStyle(TableStyle([
        ('PADDING', (0,0), (-1,-1), 0),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_baby_cards)

    # =========================================================================
    # TRANSITION TO PAGE 2
    # =========================================================================
    story.append(NextPageTemplate('Page2'))
    story.append(PageBreak())

    # =========================================================================
    # PAGE 2
    # =========================================================================

    # 1. Treatment given (table: medication, dose and route)
    story.append(section_heading("Treatment Given", space_before=0))
    story.append(Spacer(1, 3))

    treat_data = [
        [Paragraph("<b>Medication / fluid</b>", style_th), Paragraph("<b>Dose &amp; route</b>", style_th)],
        [Paragraph("IV Fluids", style_td), Paragraph("-", style_td)],
        [Paragraph("Inj. Pipzo", style_td_bold), Paragraph("4.5 g BD IV", style_td)],
        [Paragraph("Inj. Metrogyl", style_td_bold), Paragraph("100 mg BD", style_td)],
        [Paragraph("Inj. Pan", style_td_bold), Paragraph("40 BD IV", style_td)],
    ]
    t_treatment = Table(treat_data, colWidths=[100*mm, 78*mm])
    t_treatment.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#222222')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.4, colors.HexColor('#cbd5e1')),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor('#f8fafc')]),
        ('TOPPADDING', (0,0), (-1,-1), 2.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_treatment)

    # 2. Investigations (compact 5-column grid)
    story.append(section_heading("Investigations", space_before=6))
    story.append(Spacer(1, 3))

    inv_row1 = [
        cell_box("HB", "12.9 mgs %"),
        cell_box("Blood Group", "O positive"),
        cell_box("HIV", "Negative"),
        cell_box("HCV", "Negative"),
        cell_box("VDRL", "Negative"),
    ]
    inv_row2 = [
        cell_box("HBsAg", "Negative"),
        cell_box("Urea", "9.7 g/dl"),
        cell_box("Creatinine", "0.86 mg/dl"),
        cell_box("Uric Acid", "4.3 mg/dl"),
        cell_box("RBS", "100.8 mg/dl"),
    ]
    t_inv = Table([inv_row1, inv_row2], colWidths=[35.6*mm]*5)
    t_inv.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(t_inv)

    # 3. Condition on discharge (General condition, BP, Pulse, P/A in one row)
    story.append(section_heading("Condition on Discharge", space_before=6))
    story.append(Spacer(1, 3))

    cond_row = [
        cell_box("General Condition", "Good"),
        cell_box("BP", "110/70 mmHg"),
        cell_box("Pulse", "86 b/min"),
        cell_box("P/A", "Uterus contracted well; no undue bleeding P/V"),
    ]
    t_cond = Table([cond_row], colWidths=[36*mm, 32*mm, 30*mm, 80*mm])
    t_cond.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_cond)

    # 4. Discharge medications (table: medicine, frequency, duration, timing)
    story.append(section_heading("Discharge Medications", space_before=6))
    story.append(Spacer(1, 3))

    med_data = [
        [Paragraph("<b>Medicine</b>", style_th), Paragraph("<b>Frequency</b>", style_th), Paragraph("<b>Duration</b>", style_th), Paragraph("<b>Timing</b>", style_th)],
        [Paragraph("Tab. Cefixime + Sulbactam", style_td_bold), Paragraph("1-0-1", style_td), Paragraph("7 days", style_td), Paragraph("After food", style_td)],
        [Paragraph("Tab. Acendol-P", style_td_bold), Paragraph("1-0-1", style_td), Paragraph("7 days", style_td), Paragraph("After food", style_td)],
        [Paragraph("Cap. Cyra-D", style_td_bold), Paragraph("1-0-1", style_td), Paragraph("7 days", style_td), Paragraph("Before food", style_td)],
    ]
    t_med = Table(med_data, colWidths=[76*mm, 32*mm, 32*mm, 38*mm])
    t_med.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#222222')),
        ('BOX', (0,0), (-1,-1), 0.6, colors.HexColor('#94a3b8')),
        ('INNERGRID', (0,0), (-1,-1), 0.4, colors.HexColor('#cbd5e1')),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor('#f8fafc')]),
        ('TOPPADDING', (0,0), (-1,-1), 2.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_med)

    # 5. Advice (inline bullets)
    story.append(section_heading("Advice", space_before=6))
    story.append(Spacer(1, 3))
    advice_text = "&bull; Immunisation &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&bull; Exclusive breast feeding for 6 months &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&bull; Post natal exercise"
    story.append(Paragraph(advice_text, style_body_bold))

    # 6. Review and suture removal: a bordered grey bar with the date on the right
    story.append(section_heading("Review &amp; Suture Removal", space_before=6))
    story.append(Spacer(1, 3))
    p_rev_lbl = Paragraph("<b>Review after one week</b>", style_val)
    p_rev_date = Paragraph("<font size='10'><b>03/09/2026</b></font>", ParagraphStyle('RDate', parent=style_val, alignment=TA_RIGHT))
    t_review = Table([[p_rev_lbl, p_rev_date]], colWidths=[120*mm, 58*mm])
    t_review.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f1f5f9')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#000000')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(t_review)
    story.append(Spacer(1, 5))

    # 7. Highlight box: when to contact, plus emergency phone numbers in bold
    p_contact_lbl = Paragraph("<b>WHEN TO CONTACT</b>", style_lbl)
    p_contact_val = Paragraph("Report immediately if there is bleeding, fever, abdominal pain, or other emergencies.", style_body)
    p_emerg_lbl = Paragraph("<b>EMERGENCY</b>", style_lbl)
    p_emerg_val = Paragraph("<b>04342-262266 &nbsp;|&nbsp; +91-7418084266</b>", style_body_bold)

    contact_data = [
        [p_contact_lbl, p_contact_val],
        [p_emerg_lbl, p_emerg_val],
    ]
    t_contact = Table(contact_data, colWidths=[38*mm, 140*mm])
    t_contact.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#eeeeee')),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.black),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#cccccc')),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.black),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(t_contact)
    story.append(Spacer(1, 10))

    # 8. Signature row with a line above: Prepared by, doctor names and registration numbers
    sig_p1 = [
        Paragraph("<font size='7.5' color='#475569'><b>PREPARED BY</b></font>", style_lbl),
        Spacer(1, 2),
        Paragraph("<b>Staff Nurse</b>", style_val),
    ]
    sig_p2 = [
        Paragraph("<font size='9.5'><b>Dr. Karthik Rajan MS, MCh</b></font>", style_val),
        Spacer(1, 1),
        Paragraph("<font size='7.5' color='#475569'>Medical Officer / Resident Consultant Surgeon / O&amp;G</font>", style_lbl),
    ]
    sig_p3 = [
        Paragraph("<font size='9.5'><b>Dr. Ambika MS OG</b></font>", style_val),
        Spacer(1, 1),
        Paragraph("<font size='7.5' color='#475569'>Reg: TNMC-64821</font>", style_lbl),
    ]

    t_sig = Table([[sig_p1, sig_p2, sig_p3]], colWidths=[48*mm, 78*mm, 52*mm])
    t_sig.setStyle(TableStyle([
        ('LINEABOVE', (0,0), (-1,0), 0.8, colors.HexColor('#444444')),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 0),
        ('LEFTPADDING', (0,0), (-1,-1), 2),
        ('RIGHTPADDING', (0,0), (-1,-1), 2),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_sig)

    doc.build(story)
    print(f"Built {filename} successfully!")

if __name__ == "__main__":
    is_long = "--long-address" in sys.argv
    build_pdf(long_address=is_long)

