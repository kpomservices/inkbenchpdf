<?php
    require_once ('tcpdf/tcpdf.php');
    //$cwidth = 750;
    //$cheight = 600;
    $jsonData = $_POST['jsonData'];
    $cwidth = $_POST['cwidth'];
    $cheight = $_POST['cheight'];
    $rows = 1;
    $cols = 1;
    $savecrop = 'false';
    $canvasScale = 1;
    /*$cwidth = 5*96;
        $cheight = 7*96;
        $savecrop = 'false';
        $rows = 1;
        $cols = 1;*/
    $rc = $rows * $cols;
    $jsonData = json_decode($jsonData);
    //scale to 0.75 for inch based on DPI.
    $scalef = (72 / 96);
    //crop mark padding
    $cmp = 0;
    if ($savecrop != 'false')
    {
        $cmp = 10;
    }
    $pdf = new TCPDF('', 'px', array(
        $cwidth * $scalef * $cols + $cmp * 2,
        $cheight * $scalef * $rows + $cmp * 2
    ) , true, 'UTF-8', false, false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->SetLeftMargin(0);
    $pdf->SetRightMargin(0);
    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(false);
    $pdf->setCellMargins(0, 0, 0, 0);
    $pdf->SetCellPaddings(0, 0, 0, 0);
    // set auto page breaks
    $pdf->SetAutoPageBreak(false);
    $pdf->SetDisplayMode(100);
    $totalobjects = count($jsonData);
    $totalcanvas = 1;
    $offsetwidth = $cwidth * $scalef;
    $offsetheight = $cheight * $scalef;
    class Font
    {
        public $fontName;
        public $fontStyle;
        public $fontWeight;
        public $textDecoration;
    }
    for ($x = 0;$x < $totalcanvas;$x += $rc)
    {
        $pdf->AddPage();
        //$pdf->StartTransform();
        //$pdf->ScaleXY($scalef * 100);
        $colscount = 0;
        $rowscount = 0;
        for ($y = $x;$y < ($x + $rc);$y++)
        {
            $dataString = $jsonData[$y]->svg;
            $type = $jsonData[$y]->type;
            //print_r($type);
            //Replace font path to real and current path. If not than font will not be loaded
            //$dataString = str_replace("design.youprintem.com",$_SERVER['HTTP_HOST'].'/HTML5CanvasTemplateEditor/design',$dataString);
            $dataString = str_replace("https://www.krioto.com/", "../", $dataString);
            if ($colscount >= $cols)
            {
                $colscount = 0;
                $rowscount++;
            }
            /*$pdf->StartTransform();            
            $pdf->ScaleXY($scalef * 100);            
            $pdf->ImageSVG('@'.$dataString);            
            $pdf->StopTransform();            
            
            if($savecrop != 'false') {            
            $pdf->cropMark(($offsetwidth * $colscount) + $cmp, ($offsetheight * $rowscount) + $cmp, $cmp, $cmp, 'TL', array(255,0,0));            
            $pdf->cropMark(($offsetwidth * $colscount) + $offsetwidth + $cmp, ($offsetheight * $rowscount) + $cmp, $cmp, $cmp, 'TR', array(255,0,0));            
            $pdf->cropMark(($offsetwidth * $colscount) + $cmp, ($offsetheight * $rowscount) + $offsetheight + $cmp, $cmp, $cmp, 'BL', array(255,0,0));            
            $pdf->cropMark(($offsetwidth * $colscount) + $offsetwidth + $cmp, ($offsetheight * $rowscount) + $offsetheight + $cmp, $cmp, $cmp, 'BR', array(255,0,0));            
            }*/
            // start a new XObject Template and set transparency group option
            //$template_id = $pdf->startTemplate($offsetwidth * 2, $offsetheight * 2, true);
            //$pdf->StartTransform();
            // Set Clipping Mask
            //$pdf->Rect($offsetwidth, $offsetheight, $offsetwidth, $offsetheight, 'CNZ');
            //Return attribute font name
            $decoded_xml = simplexml_load_string($dataString);
            $fontArr = array();
            $fontNamesArr = array();
            //Store fonts into an array
            foreach ($decoded_xml[0] as $i => $xmlList)
            {
                $fontFamilyArr = $xmlList->text;
                $fontName = (xml_attribute($fontFamilyArr, 'font-family'));
                $fontStyle = (xml_attribute($fontFamilyArr, 'font-style'));
                $fontWeight = (xml_attribute($fontFamilyArr, 'font-weight'));
                $textDecoration = (xml_attribute($fontFamilyArr, 'text-decoration'));
                if (!in_array($fontName, $fontNamesArr))
                {
                    $localFont = new Font();
                    $localFont->fontName = $fontName;
                    $localFont->fontStyle = $fontStyle;
                    $localFont->fontWeight = $fontWeight;
                    $localFont->textDecoration = $textDecoration;
                    array_push($fontArr, $localFont);
                    array_push($fontNamesArr, $fontName);
                }
            }
            //Load neccesory fonts
            foreach ($fontArr as $localFont)
            {
                $fontFamily = $localFont->fontName;
                $fontStyle = $localFont->fontStyle;
                $fontWeight = $localFont->fontWeight;
                $textDecoration = $localFont->textDecoration;
                if ($fontFamily != '' && strlen($fontFamily) > 0)
                {
                    //$folderName = str_replace(" ","_", $fontFamily);
                    $folderName = $fontFamily;
                    $fontFileName = str_replace(" ", "", $fontFamily);
                    if ($fontStyle == "italic" && $fontWeight == "bold")
                    {
                        $fontStyle = "BoldItalic";
                    }
                    else if ($fontStyle == "italic")
                    {
                        $fontStyle = "Italic";
                    }
                    else if ($fontWeight == "bold")
                    {
                        $fontStyle = "Bold";
                    }
                    else
                    {
                        $fontStyle = "Regular";
                    }
                    $fontname = "";
                    $fontpath = "./tcpdf/fonts/googlefonts/" . $folderName . "/" . $fontFileName . "-" . $fontStyle . ".ttf";
                    if (file_exists($fontpath))
                    {
                        $fontname = TCPDF_FONTS::addTTFfont($fontpath, 'TrueTypeUnicode', '', 96);
                    }
                    else
                    {
                        $fontpath = "./tcpdf/fonts/googlefonts/" . $folderName . "/" . $fontFileName . ".ttf";
                        if (file_exists($fontpath))
                        {
                            $fontname = TCPDF_FONTS::addTTFfont($fontpath, 'TrueTypeUnicode', '', 96);
                        }
                        else
                        {
                            $fontpath = "./tcpdf/fonts/googlefonts/" . $folderName . "/" . $fontFileName . "-Regular.ttf";
                            if (file_exists($fontpath))
                            {
                                $fontname = TCPDF_FONTS::addTTFfont($fontpath, 'TrueTypeUnicode', '', 96);
                            }
                        }
                    }
                    if ($fontStyle == "Italic")
                    {
                        $fontStyle = "i";
                    }
                    elseif ($fontStyle == "Bold")
                    {
                        $fontStyle = "b";
                    }
                    else
                    {
                        $fontStyle = "";
                    }
                    $pdf->SetFont($fontname, $fontStyle, 14, '', false);
                }
            }

            //$pdf->setXY($offsetwidth, $offsetheight);
            //$pdf->ScaleXY($scalef / $canvasScale * 100);


            //$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.8, 'depth_h' => 0.8, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

            // NOTE: Uncomment the following line to rasterize SVG image using the ImageMagick library.
            //$pdf->setRasterizeVectorImages(true);

            // LAYERS

            for ($z = 0;$z < $totalobjects;$z++) {

                //$pdf->ImageSVG('@' . $jsonData[$z]);
                $svgstring = stripslashes($jsonData[$z]->svg);
                if($jsonData[$z]->type === 'textbox') {
                    $ts = $jsonData[$z]->shadow;
                    $color = @rgb_array($ts->color, false);
                    echo @$ts->blur;
                    $pdf->setTextShadow(array('enabled' => true, 'depth_w' => @$ts->offsetX, 'depth_h' => @$ts->offsetY, 'color' => @$color, 'opacity' => @$ts->blur, 'blend_mode' => 'Normal'));
                }
                //echo $svgstring;
                $pdf->ImageSVG('@' . $svgstring, $x=30, $y=100, $w='', $h=100, $link='', $align='', $palign='', $border=0, $fitonpage=false);

                if($jsonData[$z]->type === 'textbox') {
                    $ts = $jsonData[$z]->shadow;
                    $color = @rgb_array($ts->color, false);
                    $pdf->setTextShadow(array('enabled' => true, 'depth_w' => @$ts->offsetX, 'depth_h' => @$ts->offsetY, 'color' => @$color, 'opacity' => @$ts->blur, 'blend_mode' => 'Normal'));
                }
                //$pdf->setTextShadow(array('enabled' => false, 'depth_w' => 0.8, 'depth_h' => 0.8, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));


            }

            //$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.8, 'depth_h' => 0.8, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

            //$pdf->ImageSVG($file='test.svg', $x=0, $y=0, $w='', $h='', $link='http://www.tcpdf.org', $align='', $palign='', $border=1, $fitonpage=false);


            // NOTE: Uncomment the following line to rasterize SVG image using the ImageMagick library.
            //$pdf->setRasterizeVectorImages(true);

            //$pdf->ImageSVG($file='3242022182646.svg', $x=15, $y=30, $w='', $h='', $link='http://www.tcpdf.org', $align='', $palign='', $border=1, $fitonpage=false);

            //$pdf->setTextShadow(array('enabled' => false, 'depth_w' => 0.8, 'depth_h' => 0.8, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));


            //$pdf->ImageSVG($file='3242022182646.svg', $x=115, $y=130, $w='', $h='', $link='http://www.tcpdf.org', $align='', $palign='', $border=1, $fitonpage=false);

            //$pdf->StopTransform();
            // end the current Template
            //$pdf->endTemplate();
            //$pdf->printTemplate($template_id, ($offsetwidth * $colscount) - $offsetwidth + $cmp, ($offsetheight * $rowscount) - $offsetheight + $cmp, $offsetwidth * 2, $offsetheight * 2, '', '', false);

            if ($savecrop != 'false')
            {
                $pdf->cropMark(($offsetwidth * $colscount) + $cmp, ($offsetheight * $rowscount) + $cmp, $cmp, $cmp, 'TL', array(
                    136,
                    136,
                    136
                ));
                $pdf->cropMark(($offsetwidth * $colscount) + $offsetwidth + $cmp, ($offsetheight * $rowscount) + $cmp, $cmp, $cmp, 'TR', array(
                    136,
                    136,
                    136
                ));
                $pdf->cropMark(($offsetwidth * $colscount) + $cmp, ($offsetheight * $rowscount) + $offsetheight + $cmp, $cmp, $cmp, 'BL', array(
                    136,
                    136,
                    136
                ));
                $pdf->cropMark(($offsetwidth * $colscount) + $offsetwidth + $cmp, ($offsetheight * $rowscount) + $offsetheight + $cmp, $cmp, $cmp, 'BR', array(
                    136,
                    136,
                    136
                ));
            }
            //$pdf->printTemplate($template_id, ($offsetwidth * $colscount), ($offsetheight * $rowscount), $offsetwidth, $offsetheight, '', '', false);
            $colscount++;
        }
        //$pdf->StopTransform();
    }
    $pdf->Close();
    //$filename = $_SERVER['DOCUMENT_ROOT'] . "inkbenchpdf/outputpdfs/" . 'output.pdf';
    $filename = $_SERVER['DOCUMENT_ROOT'] . "inkbenchpdf/outputpdfs/" . $_POST['filename'];
    //$filename = $_SERVER['DOCUMENT_ROOT'] . "gitup/kpomservices/inkbenchpdf/outputpdfs/" . $_POST['filename'];
    $pdf->Output($filename, 'F');
    echo $filename;
    //$filename = $_SERVER['DOCUMENT_ROOT'] . "HTML5CanvasTemplateEditor/design/admin/outputpdfs/temp.pdf";
    //echo $pdf->Output('temp.pdf', 'D');
    
    function rgb_array($color, $include_alpha = true) {
        return array_slice(preg_split('~[^\d.]+~', $color, -1, PREG_SPLIT_NO_EMPTY), 0, $include_alpha + 3);
    }

    function Hex2RGB($color)
    {
        $color = str_replace('#', '', $color);
        if (strlen($color) != 6)
        {
            return array(
                0,
                0,
                0
            );
        }
        $rgb = array();
        for ($x = 0;$x < 3;$x++)
        {
            $rgb[$x] = hexdec(substr($color, (2 * $x) , 2));
        }
        return $rgb;
    }
    function xml_attribute($object, $attribute)
    {
        if (isset($object[$attribute])) return (string)$object[$attribute];
    }
?>
