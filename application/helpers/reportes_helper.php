<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function getPlantillaGeneralReporte($arrContent,$datos,$paramPageOrientation=FALSE,$paramPageSize=FALSE,$arrPageMargins=FALSE,$arrConfig = FALSE){
    $ci2 =& get_instance();
    if( empty($arrConfig) ){
        $fConfig = $ci2->model_config->m_cargar_empresa_usuario_activa(); 
        // $fConfig['mode_report'] = FALSE;
    }else{ 
        $fConfig = $arrConfig;
    }
    // $fConfig = $ci2->model_config->m_cargar_empresa_usuario_activa();
    $arrImages = array( 
      'imageHeaderPage'=> convertImageToBase64('assets/img/dinamic/empresa/'.$fConfig['nombre_logo']) // base_url('assets/img/dinamic/empresa/'.$fConfig['nombre_logo'])
    );
    $arrHeader = array( 
      	array( 
  	      	'text'=> 'USUARIO: '.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESIÓN: '.date('Y-m-d H:i:s'),
  	      	'style'=> 'headerPage'
      	)
  	);
    $arrFooter = array();
    $arrStyles = array( 
      	'headerTitle'=> array( 
	        'fontSize'=> 17,
	        'bold'=> true
	        // 'alignment'=> 'center'
      	),
      	'filterTitle'=> array( 
	        'fontSize'=> 12,
	        'bold'=> true
	        // 'alignment'=> 'center'
      	),
      	'headerPage'=> array(
	        'fontSize'=> 6,
	        'alignment'=> 'right',
	        'italic' => true
      	),
      	'tableHeader'=> array(
    			'bold'=> true,
    			'fontSize'=> 10,
    			'color'=> 'black'
    		),
        'tableHeaderLG' => array(
          'bold'=> true,
          'fontSize'=> 12,
          'color'=> 'black',
          'alignment'=> 'center'
        )
    );
    // var_dump($fConfig); exit(); 
    $strLines = null;
    for ($i=0; $i < 148; $i++) { 
      $strLines.= '_';
    }
    // $arrContent = array(
      
    // );
    $arrMainContent = array( 
    	array(
	    	'image'=> 'imageHeaderPage',
	      	'alignment'=> 'left',
	      	'width' => 150,
	      	'margin' => array(-30,-5,0,0)
	    ),
	    array( 
	    	'text'=> $fConfig['razon_social'],
	    	'style'=> array('headerPage',array('alignment'=> 'left','fontSize'=> 8) ),
	    	'margin' => array(13,-15,0,0)
	    ),
	    array( 
	    	'text'=> $fConfig['domicilio_fiscal'],
	    	'style'=> array('headerPage',array('alignment'=> 'left','fontSize'=> 4) ),
	    	'margin' => array(13,0,0,0)
	    ),
	    array(
	        'text'=> $datos['titulo'],
	        'style'=> 'headerTitle',
	        'margin'=> array(270,-20,0,0)
	    ),
	    array( 
	        'text'=> $strLines,
	        'margin'=> array(-30,0,0,0)
	    )
    );
    $arrDataPDF = array( 
      //'background'=> null,
      'header'=> $arrHeader,
      'footer'=> $arrFooter,
      'content'=> array_merge($arrMainContent,$arrContent),
      'styles' => $arrStyles,
      'images' => $arrImages,
      'pageSize' => ($paramPageSize === FALSE ? 'A4' : $paramPageSize), 
      'pageOrientation' => ($paramPageOrientation === FALSE ? 'portrait' : $paramPageOrientation),  // $paramPageOrientation || 'portrait', // portrait/landscape
      'pageMargins' => ($arrPageMargins === FALSE ? array(50, 12, 20, 12) : $arrPageMargins)  // [left, top, right, bottom] or [horizontal, vertical] or just a number for equal margins
    );
    return $arrDataPDF;
    // return $fData['id'];
}
function getPlantillaGeneralReporteHTML($objPdf,$htmlContent,$datos,$paramPageOrientation=FALSE,$paramPageSize=FALSE,$arrPageMargins=FALSE)
{
  $ci2 =& get_instance();
  $fConfig = $ci2->model_config->m_cargar_empresa_usuario_activa(); 
  $style = ' <style>
      @page { margin-top: 0.2cm; margin-left: 0.7cm; margin-right: 0.5cm; margin-bottom: 0.6cm; }
      .header-mini { font-size: 6px; text-align: right; }
      .header-logo { margin-top: -8px;} 
      .block { display: block !important;}
      .headerTitle { font-size: 20px; font-weight:bold; margin-left: 360px; margin-top: -28px; color: #313a3e; }
      .razon_social { font-size: 10px; margin-left: 58px; margin-top: -20px; }
      .domicilio_fiscal { font-size: 5px; margin-left: 58px; margin-top: -1px; }
      .filterTitle { font-size: 12px; font-weight; bold; } 
      .text-center { text-align: center; }
      table {
        margin-top: 2pt;
        margin-bottom: 5pt;
        border-collapse: collapse;
      }
      thead td, thead th, tfoot td, tfoot th {
          font-variant: small-caps;
      }

      table.mainTable th { 
          vertical-align: top;
          padding-top: 3mm;
          padding-bottom: 3mm;
      }
      table.subTable th { 
        font-size: 11px;
      }
      table.detalleTable th { 
        font-size: 11px;
      }
      table.detalleTable td { 
        font-size: 10px;

      }
      table.detalleTable tfoot td { 
        font-size: 10px;
        font-weight: bold;
      }
    </style>
  ';
  $htmlPlantilla = '';
  $htmlPlantilla .= '<html> <head> '.$style.' </head> <body>';
  $htmlPlantilla .= '<div class="header-mini"> USUARIO:'.strtoupper($ci2->sessionHospital['username']).'    /   FECHA DE IMPRESIÓN: '.date('Y-m-d H:i:s') .'</div>';
  $htmlPlantilla .= '<div class="header-logo"> <img width="200" src="'.base_url('assets/img/dinamic/empresa/'.$fConfig['nombre_logo']).'" /> '; 
  $htmlPlantilla .= '<div class="block razon_social">'.$fConfig['razon_social'].'</div>';
  $htmlPlantilla .= '<div class="block domicilio_fiscal">'.$fConfig['domicilio_fiscal'].'</div>';
  $htmlPlantilla .= '</div>';
  $htmlPlantilla .= '<div class="headerTitle">'.$datos['titulo'].'</div> <hr />';
  $htmlPlantilla .= $htmlContent;
  $htmlPlantilla .= '</body></html>';
  return $htmlPlantilla;
}