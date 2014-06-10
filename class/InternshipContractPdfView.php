<?php

/**
 * InternshipContractPdfView
 *
 * View class for generating a PDF of an internship.
 *
 * @author jbooker
 * @package Intern
 */

class InternshipContractPdfView {

    private $internship;
    private $emergencyContacts;

    private $pdf;

    /**
     * Creates a new InternshipContractPdfView
     *
     * @param Internship $i
     * @param Array<EmergencyContact> $emergencyContact
     */
    public function __construct(Internship $i, Array $emergencyContacts)
    {
        $this->internship = $i;
        $this->emergencyContacts = $emergencyContacts;

        require_once(PHPWS_SOURCE_DIR . 'mod/intern/pdf/fpdf.php');
        require_once(PHPWS_SOURCE_DIR . 'mod/intern/pdf/fpdi.php');

        PHPWS_Core::initModClass('intern', 'Term.php');

        $this->generatePdf();
    }

    /**
     * Returns the FPDI (FPDF) object which was generated by this view.
     *
     * @return FPDI
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Does the hard work of generating a PDF.
     */
    private function generatePdf()
    {
        $this->pdf = new FPDI('P', 'mm', 'Letter');
        $a = $this->internship->getAgency();
        $d = $this->internship->getDepartment();
        $f = $this->internship->getFaculty();
        $m = $this->internship->getUgradMajor();
        $g = $this->internship->getGradProgram();
        $subject = $this->internship->getSubject();

        $pagecount = $this->pdf->setSourceFile(PHPWS_SOURCE_DIR . 'mod/intern/pdf/ASU_Internship_Contract_101711_v7c-5-28-14.pdf');
        $tplidx = $this->pdf->importPage(1);
        $this->pdf->addPage();
        $this->pdf->useTemplate($tplidx);

        $this->pdf->setFont('Times', null, 10);

        /**************************
         * Internship information *
        */

        // Term
        $this->pdf->setXY(128, 39);
        $this->pdf->cell(60, 5, Term::rawToRead($this->internship->getTerm(), false));

        /* Department */
        //$this->pdf->setFont('Times', null, 10);
        $this->pdf->setXY(171, 40);
        $this->pdf->MultiCell(31, 3, $subject->getAbbreviation());

        // Subject and Course #
        //$this->pdf->setFont('Times', null, 8);
        $this->pdf->setXY(132, 49);
        $course_info = $this->internship->getCourseNumber();
        $this->pdf->cell(59, 5, $course_info);

        // Section #
        $this->pdf->setXY(178, 49);
        $this->pdf->cell(25, 5, $this->internship->getCourseSection());

        /*
         $this->pdf->setXY(132, 39);
        if (!is_null($m)) {
        $major = $m->getName();
        } else {
        $major = 'N/A';
        }
        $this->pdf->cell(73, 5, $major);
        */

        //$this->pdf->setFont('Times', null, 10);
        $this->pdf->setXY(140, 48);
        $this->pdf->cell(73, 6, $this->internship->getCourseTitle());

        /* Location */
        if($this->internship->isDomestic()){
            $this->pdf->setXY(85, 64);
            $this->pdf->cell(12, 5, 'X');
        }
        if($this->internship->isInternational()){
            $this->pdf->setXY(156, 64);
            $this->pdf->cell(12, 5, 'X');
        }

        /**
         * Student information.
         */
        $this->pdf->setXY(40, 81);
        $this->pdf->cell(55, 5, $this->internship->getFullName());

        $this->pdf->setXY(173,81);
        $this->pdf->cell(54,5, $this->internship->getGpa());

        $this->pdf->setXY(32, 87);
        $this->pdf->cell(42, 5, $this->internship->getBannerId());

        $this->pdf->setXY(41, 92);
        $this->pdf->cell(54, 5, $this->internship->getEmailAddress() . '@appstate.edu');

        $this->pdf->setXY(113, 92);
        $this->pdf->cell(54, 5, $this->internship->getPhoneNumber());

        /* Student Address */
        $this->pdf->setXY(105, 87);
        $this->pdf->cell(54, 5, $this->internship->getStudentAddress());


        /* Payment */
        if($this->internship->isPaid()){
            $this->pdf->setXY(160, 92);
            $this->pdf->cell(10,5, 'X');
        }

        if($this->internship->isUnPaid()){
            $this->pdf->setXY(190, 92);
            $this->pdf->cell(10,5,'X');
        }

        /* Start/end dates */
        //$this->pdf->setFont('Times', null, 10);
        $this->pdf->setXY(50, 97);
        $this->pdf->cell(25, 5, $this->internship->getStartDate(true));
        $this->pdf->setXY(93, 97);
        $this->pdf->cell(25, 5, $this->internship->getEndDate(true));

        /* Hours */
        $this->pdf->setXY(193, 97);
        $this->pdf->cell(12, 5, $this->internship->getCreditHours());
        $this->pdf->setXY(157, 97);
        $this->pdf->cell(12, 5, $this->internship->getAvgHoursPerWeek()); // hours per week

        //        $this->pdf->cell(35, 5, 'Graduate Program:', 'LTB');
        //        if (!is_null($g)) {
        //            $this->pdf->cell(155, 5, $g->getName(), 'RTB');
        //        } else {
        //            $this->pdf->cell(155, 5, 'N/A', 'RTB');
        //        }
        //

        /***
         * Faculty supervisor information.
         */
        if(isset($f)){
            $this->pdf->setXY(26, 118);
            $this->pdf->cell(81, 5, $f->getFullName());

            $this->pdf->setXY(29, 125);
            $this->pdf->cell(81, 5, $f->getStreetAddress1());

            $this->pdf->setXY(15, 132);
            $this->pdf->cell(81, 5, $f->getStreetAddress2());
            
            $this->pdf->setXY(60, 132);
            $this->pdf->cell(81, 5, $f->getCity());
            
            $this->pdf->setXY(88, 132);
            $this->pdf->cell(81, 5, $f->getState());
            
            $this->pdf->setXY(95, 132);
            $this->pdf->cell(81, 5, $f->getZip());

            $this->pdf->setXY(26, 139);
            $this->pdf->cell(77, 5, $f->getPhone());

            $this->pdf->setXY(25, 146);
            $this->pdf->cell(77, 5, $f->getFax());

            $this->pdf->setXY(26, 154);
            $this->pdf->cell(77, 5, $f->getUsername() . '@appstate.edu');
        }

        /***
         * Agency information.
        */
        $this->pdf->setXY(133, 116);
        $this->pdf->cell(71, 5, $a->getName());

        $agency_address = $a->getAddress();

        //TODO: make this smarter so it adds the line break between words
        if(strlen($agency_address) < 49){
            // If it's short enough, just write it
            $this->pdf->setXY(125, 121);
            $this->pdf->cell(77, 5, $agency_address);
        }else{
            // Too long, need to use two lines
            $agencyLine1 = substr($agency_address, 0, 49); // get first 50 chars
            $agencyLine2 = substr($agency_address, 49); // get the rest, hope it fits

            $this->pdf->setXY(125, 121);
            $this->pdf->cell(77, 5, $agencyLine1);
            $this->pdf->setXY(110, 126);
            $this->pdf->cell(77, 5, $agencyLine2);
        }

        /**
         * Agency supervisor info.
         */
        $this->pdf->setXY(110, 136);
        $super = "";
        $superName = $a->getSupervisorFullName();
        if(isset($superName) && !empty($superName) && $superName != ''){
            //test('ohh hai',1);
            $super .= $a->getSupervisorFullName();
        }

        $supervisorTitle = $a->getSupervisorTitle();

        if(isset($a->supervisor_title) && !empty($a->supervisor_title)){
            $super .= ', ' . $supervisorTitle;
        }
        $this->pdf->cell(75, 5, $super);

        $this->pdf->setXY(124, 142);
        $this->pdf->cell(78, 5, $a->getSuperAddress());

        $this->pdf->setXY(122, 152);
        $this->pdf->cell(72, 5, $a->getSupervisorEmail());

        $this->pdf->setXY(122, 147);
        $this->pdf->cell(33, 5, $a->getSupervisorPhoneNumber());

        $this->pdf->setXY(163, 147);
        $this->pdf->cell(40, 5, $a->getSupervisorFaxNumber());

        /* Internship Location */
        $internshipAddress = trim($this->internship->getStreetAddress());
        $agencyAddress = trim($a->getStreetAddress());

        if($internshipAddress != '' && $agencyAddress != '' && $internshipAddress != $agencyAddress) {
            $this->pdf->setXY(110, 154);
            $this->pdf->cell(52, 5, $this->internship->getLocationAddress());
        }


        /**********
         * Page 2 *
        **********/
        $tplidx = $this->pdf->importPage(2);
        $this->pdf->addPage();
        $this->pdf->useTemplate($tplidx);

        /* Emergency Contact Info */
        if(sizeof($this->emergencyContacts) > 0){
            $firstContact = $this->emergencyContacts[0];

            $this->pdf->setXY(60, 259);
            $this->pdf->cell(52, 0, $firstContact->getName());

            $this->pdf->setXY(134, 259);
            $this->pdf->cell(52, 0, $firstContact->getRelation());

            $this->pdf->setXY(175, 259);
            $this->pdf->cell(52, 0, $firstContact->getPhone());
        }
    }
}

?>
