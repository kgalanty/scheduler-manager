<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
class ReportsPDFWrapper
{
    public $pdf, $config, $admins;
    public function __construct($admins = [], $config = [])
    {
        $this->pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
        $this->admins = $admins;
        $this->config = $config;
        $this->fillPages();
       
    }
    public function fillPages()
    {
       // echo('<pre>');var_dump($this->admins); die;
       // foreach($this->admins as $k=>$admin)
        //{
            $this->AddPage($this->admins, $this->config['cells']);
       // }
    }
    public function render()
    {
        $this->pdf->writeHTML('<h3>'.$this->config['dates'][0].' - '.$this->config['dates'][count($this->config['dates'])-1].'</h3>', true, false, true, false, '');
      
        $this->pdf->SetFillColor(255, 0, 0);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(128, 0, 0);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->SetFont('', 'B');
          // Header
          $w = 28;
        
          $num_headers = count($this->config['dates']);
          for($i = 0; $i < $num_headers; ++$i) {
              $this->pdf->Cell($w, 7, $this->config['dates'][$i], 1, 0, 'C', 1);
          }
          $this->pdf->Ln();
          $this->pdf->SetFillColor(224, 235, 255);
          $this->pdf->SetTextColor(0);
          $this->pdf->SetFont('');
          $fill = 0;
          foreach($data as $row) {
            for($i = 0; $i < $num_headers; ++$i) {
                $this->pdf->Cell($w, 2, $row[$i]??'', 1, 0, 'C', $fill);
               // $this->pdf->MultiCell(55, 5, "[CENTER] \n".$row[$i], 1, 'C', 0, 0, '', '', true);
          }
          $this->pdf->Ln();
            $fill=!$fill;
        }
          // Color and font restoration
          $this->pdf->SetFillColor(224, 235, 255);
          $this->pdf->SetTextColor(0);
          $this->pdf->SetFont('');
    }
    public function AddPage($admins, $data)
    {
        
        $this->pdf->AddPage();                    // pretty self-explanatory
        //$this->pdf->writeHTML('<h2>'.$admin->firstname.' '.$admin->lastname.'</h2>', true, false, true, false, '');
        $this->pdf->SetFont('', 'B');
        $this->pdf->writeHTML('<h3>'.$this->config['dates'][0].' - '.$this->config['dates'][count($this->config['dates'])-1].'</h3>', true, false, true, false, '');
        $AllShifts = \collect($this->config['AllShifts'])->keyBy('id');
        //echo('<pre>'); var_dump();die;
       
        foreach($data as $k=>$dd) 
        {
            $this->pdf->SetFont('', 'B', $this->pdf->pixelsToUnits('24'));
            //echo('<pre>'); var_dump($dd);die;
            $this->pdf->writeHTML('<h3>'.$AllShifts[$this->config['shiftsCells'][$k]]->from.'-'.$AllShifts[$this->config['shiftsCells'][$k]]->to.'</h3>', true, false, true, false, '');
           // echo('<pre>'); var_dump($dd);die;
            $this->pdf->SetFillColor(155, 155, 155);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetDrawColor(0, 0, 0);
            $this->pdf->SetLineWidth(0.1);
           
            // Header
            $w = 28;
    
            $num_headers = count($this->config['dates']);
            for($i = 0; $i < $num_headers; ++$i) {
                $this->pdf->Cell($w, 7, $this->config['dates'][$i], 1, 0, 'C', 1);
            }
            $this->pdf->Ln();
            $this->pdf->SetFillColor(224, 235, 255);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetFont('');
            $fill = 0;
            foreach($dd as $row) {
                for($i = 0; $i < $num_headers; ++$i) {
                    $this->pdf->Cell($w, 2, $row[$i]->firstname.' '.$row[$i]->lastname, 1, 0, 'C', $fill);
                }
                $this->pdf->Ln();
            }
           
                $fill=!$fill;
            // Color and font restoration
            // $this->pdf->SetFillColor(224, 235, 255);
            // $this->pdf->SetTextColor(0);
            // $this->pdf->SetFont('');
            $this->pdf->Ln();
        }
    }
    public function releasePDF()
    {
        $attachment_path = tempnam(sys_get_temp_dir(), 'report.pdf');
        $this->pdf->Output($attachment_path, 'F');
        return $attachment_path;
    }
}