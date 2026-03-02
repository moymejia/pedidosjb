
<?php
class GenerarXml {
	public function ToXML($transaccion)
	{
		$respuesta = new Respuesta();
		 

		if (get_class($transaccion)=="DocumentoFel")
		{
                    
			$documento_fel = $transaccion;
		 
			$Exportacion = $documento_fel->getDatosGenerales()->getExportacion();
            $etiqueta_exportacion = "";

             // SECCION DE DATOS GENERALES   

             if ($Exportacion != NULL) {
                    $etiqueta_exportacion = "Exp=\"" . $Exportacion . "\"";
                    p_log("De exportacion");
             }

             $NumeroAcceso = $documento_fel->getDatosGenerales()->getNumeroAcceso();
             $etiqueta_numero_acceso = "";

             if ($NumeroAcceso != NULL)
             {
             	$etiqueta_numero_acceso = "NumeroAcceso=\"" . $NumeroAcceso . "\"";
             }

             $FechaHoraEmision = $documento_fel->getDatosGenerales()->getFechaHoraEmision();
             $CodigoMoneda = $documento_fel->getDatosGenerales()->getCodigoMoneda();
             $Tipo = $documento_fel->getDatosGenerales()->getTipo();

             // SECCION DE DATOS DEL EMISOR

             $CorreoEmisor = $documento_fel->getDatosEmisor()->getCorreoEmisor();
             $etiqueta_correo_emisor = "";

              if ($CorreoEmisor != NULL) {
              	$etiqueta_correo_emisor = "CorreoEmisor=\"" . $CorreoEmisor . "\"";
              }

            $CodigoEstablecimiento = $documento_fel->getDatosEmisor()->getCodigoEstablecimiento();
            $NITEmisor = $documento_fel->getDatosEmisor()->getNITEmisor();
            $NombreComercial = $documento_fel->getDatosEmisor()->getNombreComercial();
            $AfiliacionIVA = $documento_fel->getDatosEmisor()->getAfiliacionIVA();
            $NombreEmisor = $documento_fel->getDatosEmisor()->getNombreEmisor();
            $Direccion = $documento_fel->getDatosEmisor()->getDireccion();
            $CodigoPostal = $documento_fel->getDatosEmisor()->getCodigoPostal();
            $Municipio = $documento_fel->getDatosEmisor()->getMunicipio();
            $Departamento = $documento_fel->getDatosEmisor()->getDepartamento();
            $Pais = $documento_fel->getDatosEmisor()->getPais();
			
			// SECCION DE DATOS DEL RECEPTOR
            $CorreoReceptor = $documento_fel->getDatosReceptor()->getCorreoReceptor();
            $etiqueta_correo_receptor = "";

            if ($CorreoReceptor != NULL) {
             	$etiqueta_correo_receptor = "CorreoReceptor=\"" . $CorreoReceptor . "\"";
            }

            $TipoEspecial = $documento_fel->getDatosReceptor()->getTipoEspecial();
            $etiqueta_tipo_especial = "";

            if ($TipoEspecial != NULL) {
            	$etiqueta_tipo_especial = "TipoEspecial=\"" . $TipoEspecial . "\"";
            }

            $IDReceptor = $documento_fel->getDatosReceptor()->getIDReceptor();
            $NombreReceptor = $documento_fel->getDatosReceptor()->getNombreReceptor();
            $DireccionReceptor = $documento_fel->getDatosReceptor()->getDireccion();
            $CodigoPostalReceptor = $documento_fel->getDatosReceptor()->getCodigoPostal();
            $MunicipioReceptor = $documento_fel->getDatosReceptor()->getMunicipio();
            $DepartamentoReceptor = $documento_fel->getDatosReceptor()->getDepartamento();
            $PaisReceptor = $documento_fel->getDatosReceptor()->getPais();

            $gran_total = $documento_fel->getTotales()->getGranTotal();

            $xml_fel = "";
            $xml_fel_formateado = "";

            // Plantilla Para Generacion de XML en FEL
            $xml_fel = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                    . "<dte:GTDocumento xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:dte=\"http://www.sat.gob.gt/dte/fel/0.2.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" Version=\"0.1\" xsi:schemaLocation=\"http://www.sat.gob.gt/dte/fel/0.2.0\">\n"
                    . "  <dte:SAT ClaseDocumento=\"dte\">\n"
                    . "    <dte:DTE ID=\"DatosCertificados\">\n"
                    . "      <dte:DatosEmision ID=\"DatosEmision\">\n"
                    . "        <dte:DatosGenerales FechaHoraEmision=\"" . $FechaHoraEmision . "\" " . $etiqueta_exportacion . " " . $etiqueta_numero_acceso . " CodigoMoneda=\"" . $CodigoMoneda . "\" Tipo=\"" . $Tipo . "\"/>\n"
                    . "        <dte:Emisor " . $etiqueta_correo_emisor . " CodigoEstablecimiento=\"" . $CodigoEstablecimiento . "\" NITEmisor=\"" . $NITEmisor . "\" NombreComercial=\"" . $NombreComercial . "\" AfiliacionIVA=\"" . $AfiliacionIVA . "\" NombreEmisor=\"" . $NombreEmisor . "\">\n"
                    . "          <dte:DireccionEmisor>\n"
                    . "            <dte:Direccion>" . $Direccion . "</dte:Direccion>\n"
                    . "            <dte:CodigoPostal>" . $CodigoPostal . "</dte:CodigoPostal>\n"
                    . "            <dte:Municipio>" . $Municipio . "</dte:Municipio>\n"
                    . "            <dte:Departamento>" . $Departamento . "</dte:Departamento>\n"
                    . "            <dte:Pais>" . $Pais . "</dte:Pais>\n"
                    . "          </dte:DireccionEmisor>\n"
                    . "        </dte:Emisor>\n"
                    . "        <dte:Receptor IDReceptor=\"" . $IDReceptor . "\" " . $etiqueta_correo_receptor . " " . $etiqueta_tipo_especial . " NombreReceptor=\"" . $NombreReceptor . "\">\n"
                    . "          <dte:DireccionReceptor>\n"
                    . "            <dte:Direccion>" . $DireccionReceptor . "</dte:Direccion>\n"
                    . "            <dte:CodigoPostal>" . $CodigoPostalReceptor . "</dte:CodigoPostal>\n"
                    . "            <dte:Municipio>" . $MunicipioReceptor . "</dte:Municipio>\n"
                    . "            <dte:Departamento>" . $DepartamentoReceptor . "</dte:Departamento>\n"
                    . "            <dte:Pais>" . $PaisReceptor . "</dte:Pais>\n"
                    . "          </dte:DireccionReceptor>\n"
                    . "        </dte:Receptor>\n"
                    . "";



        	// Inicia Parte 1 | Formacion dinamica del XML | Seccion de Frases
            $cantidad_frases = count($documento_fel->getFrases());

            if ($cantidad_frases == 0) {
            // No ejecuta ninguna accion, las etiquetas al estar vacias no apareceran
            } else {

                $xml_fel = $xml_fel . "<dte:Frases>";

                for ($f = 0; $f <  $cantidad_frases; $f++) {

                    if($documento_fel->getFrases()[$f]->getFecha()==null){
                    $xml_fel = $xml_fel . "<dte:Frase CodigoEscenario=\"" . $documento_fel->getFrases()[$f]->getCodigoEscenario() . "\" TipoFrase=\"" . $documento_fel->getFrases()[$f]->getTipoFrase() . "\"/>";
                }
                 else{
                    $xml_fel = $xml_fel . "<dte:Frase CodigoEscenario=\"" . $documento_fel->getFrases()[$f]->getCodigoEscenario() . "\"
                    FechaResolucion=\"" . $documento_fel->getFrases()[$f]->getFecha() . "\"
                    NumeroResolucion=\"" . $documento_fel->getFrases()[$f]->getResolucion() . "\"

                     TipoFrase=\"" . $documento_fel->getFrases()[$f]->getTipoFrase() . "\"/>";
                }

                }


                $xml_fel = $xml_fel . "</dte:Frases>";
            }   

            // Finaliza parate 2 | Formacion dinamica XML | Seccion de Frases

            // Inicia Parte 2 | Formacion dinamica del XML | Seccion de Detalles
            $cantidad_detalles = count($documento_fel->getItems());

           	if ($cantidad_detalles == 0) {
                    // No ejecuta ninguna accion, las etiquetas al estar vacias no apareceran
                } else {

                    $xml_fel = $xml_fel . "<dte:Items>";
                    
                    foreach ($documento_fel->getItems() as $item) {

                        // Etiqueta de Descuento
                        $etiqueta_descuento = "";
                        if ($item->getDescuento() != NULL) {
                            
                            $etiqueta_descuento = "<dte:Descuento>".$item->getDescuento()."</dte:Descuento>\n";
                            
                        }else{
                            $etiqueta_descuento = "<dte:Descuento>"."0"."</dte:Descuento>\n";
                        }
                        
                        //var_dump("Descuento Fuera de IF: ".$item->getDescuento());

                        // Etiqueta para la Unidad de Medida
                        $etiqueta_unidad_medida = "";
                        if ($item->getUnidadMedida() != NULL) {
                            $etiqueta_unidad_medida = "<dte:UnidadMedida>" . $item->getUnidadMedida() . "</dte:UnidadMedida>\n";
                        }

                        $xml_fel = $xml_fel . ""
                                . "          <dte:Item NumeroLinea=\"" . $item->getNumeroLinea() . "\" BienOServicio=\"" . $item->getBienOServicio() . "\">\n"
                                . "            <dte:Cantidad>" . $item->getCantidad() . "</dte:Cantidad>\n"
                                . $etiqueta_unidad_medida
                                . "            <dte:Descripcion>" . $item->getDescripcion() . "</dte:Descripcion>\n"
                                . "            <dte:PrecioUnitario>" . $item->getPrecioUnitario() . "</dte:PrecioUnitario>\n"
                                . "            <dte:Precio>" . $item->getPrecio() . "</dte:Precio>\n"
                                . $etiqueta_descuento;
                               // var_dump($etiqueta_descuento);
                                //var_dump($etiqueta_unidad_medida);

                        // Inicia Parte 3 | Formacion dinamica del XML | Seccion de Impuestos por Detalle
                        $cantidad_impuestos = count($item->getImpuestosDetalle());   
                        if ($cantidad_impuestos == 0) {
                            // No ejecuta ninguna accion.
                        } else {

                            $xml_fel = $xml_fel . "<dte:Impuestos>";
                            foreach ($item->getImpuestosDetalle() as $impuesto_detalle) {                           

                                // Etiqueta para la Cantidad de Unidades Gravables
                                $etiqueta_cantidad_unidades_gravables = "";
                                if ($impuesto_detalle->getCantidadUnidadesGravables() != NULL) {
                                    $etiqueta_cantidad_unidades_gravables = "<dte:CantidadUnidadesGravables>" . $impuesto_detalle->getCantidadUnidadesGravables() . "</dte:CantidadUnidadesGravables>\n";
                                }

                                // Etiqueta para el MontoGravable
                                $etiqueta_monto_gravable = "";
                                if ($impuesto_detalle->getMontoGravable() != NULL) {
                                    $etiqueta_monto_gravable = "<dte:MontoGravable>" . $impuesto_detalle->getMontoGravable() . "</dte:MontoGravable>\n";
                                }

                                $xml_fel = $xml_fel . ""
                                        . "              <dte:Impuesto>\n"
                                        . "                <dte:NombreCorto>" . $impuesto_detalle->getNombreCorto() . "</dte:NombreCorto>\n"
                                        . "                <dte:CodigoUnidadGravable>" . $impuesto_detalle->getCodigoUnidadGravable() . "</dte:CodigoUnidadGravable>\n"
                                        . $etiqueta_cantidad_unidades_gravables
                                        . $etiqueta_monto_gravable
                                        . "                <dte:MontoImpuesto>" . $impuesto_detalle->getMontoImpuesto() . "</dte:MontoImpuesto>\n"
                                        . "              </dte:Impuesto>\n";
                            }

                            $xml_fel = $xml_fel . "</dte:Impuestos>";

                        }
                        // Finaliza Parte 3 | Formacion dinamica del XML | Seccion de Impuestos por Detalle

                        $xml_fel = $xml_fel
                                . "            <dte:Total>" . $item->getTotal() . "</dte:Total>\n"
                                . "          </dte:Item>\n";
                    }
                    $xml_fel = $xml_fel . "</dte:Items>";
                }
                // Finaliza Parte 2 | Formacion dinamica del XML | Seccion de Detalles
                 $xml_fel = $xml_fel
                            . "        <dte:Totales>\n";

                 // Inicia Parte 4 | Formacion dinamica del XML | Seccion de Resumenes de Impuestos
                

                $cantidad_total_impuestos = count($documento_fel->getImpuestosResumen());

                if ($cantidad_total_impuestos == 0) {
                    // No ejecuta ninguna accion, las etiquetas al estar vacias no apareceran
                } else {

                    $xml_fel = $xml_fel . "<dte:TotalImpuestos>";

                    
                    foreach ($documento_fel->getImpuestosResumen() as $impuesto_resumen) {
                        $xml_fel = $xml_fel . "<dte:TotalImpuesto NombreCorto=\"" . $impuesto_resumen->getNombreCorto() . "\" TotalMontoImpuesto=\"" . $impuesto_resumen->getTotalMontoImpuesto() . "\"/>";
                    }
                    $xml_fel = $xml_fel . "</dte:TotalImpuestos>";
                }
                // Finaliza Parte 4 | Formacion dinamica del XML | Seccion de Resumenes de Impuestos

                $xml_fel = $xml_fel
                            . "		<dte:GranTotal>" . $gran_total . "</dte:GranTotal>\n"
                            . "        </dte:Totales>\n";

                $cantidad_complementos = count($documento_fel->getComplementos());

                 if ($cantidad_complementos == 0) {
                        // Sino hay complementos no pasa nada, pues el nodo aplica para unos documentos y para otros no.
                } else {
                    // Si existe mas de algun complemento entonces se verifica cual es y se aplican las validaciones correspondientes

                    $xml_fel = $xml_fel . "<dte:Complementos>";

                    for ($comp = 0; $comp < $cantidad_complementos; $comp++) {

                        $xml_fel = $xml_fel . $this->imprime_complementos($documento_fel->getComplementos()[$comp], $comp);
                    }

                    $xml_fel = $xml_fel . "</dte:Complementos>";

                }

                $xml_fel = $xml_fel
                            . "      </dte:DatosEmision>\n"
                            . "    </dte:DTE>\n";

                
               //var_dump($documento_fel->getAdenda());
			//   $cantidad_adendas=0;
		$cantidad_adendas = count($documento_fel->getAdendaX());
              // $cantidad_adendas = $documento_fel->getAdenda();
			   
               
                
                //echo "LA CANTIDAD DE ADENDAS ES DE:".$cantidad_adendas;

                if ($cantidad_adendas == 0) {
                        // sino hay adendas, no realiza ninguna accion
                } else {

                   $xml_fel = $xml_fel . " <dte:Adenda>\n" ;

                    /*for (Map.Entry m : documento_fel.getAdenda().getAdenda().entrySet()) {
                        xml_fel = xml_fel + "<"+m.getKey().toString().toLowerCase()+">"+m.getValue()+"</"+m.getKey().toString().toLowerCase()+">\n";
                    }*/
                  
				  
				//  $xml_fel = $xml_fel . "<".Adenda1.">".$documento_fel->Adendas()->getTamanio()."</".Adenda1.">\n";
	            	foreach ($documento_fel->getAdendaX() as $actual) {
						  //$xml_fel = $xml_fel . "<".$key.">". $value ."</" . $key .">\n";
						  $xml_fel = $xml_fel . "<".$actual->getNombre().">". $actual->getValor() ."</" . $actual->getNombre() .">\n";
					}
                   

                   $xml_fel = $xml_fel . " </dte:Adenda>";

                }

                $xml_fel = $xml_fel . "  </dte:SAT>\n"
                         /*   . "<ds:Signature Id=\"ID1\">\n"
                            . "		<ds:SignedInfo>\n"
                            . "			<ds:CanonicalizationMethod Algorithm=\"http://www.altova.com/\">\n"
                            . "			</ds:CanonicalizationMethod>\n"
                            . "			<ds:SignatureMethod Algorithm=\"http://www.altova.com/\">\n"
                            . "			</ds:SignatureMethod>\n"
                            . "			<ds:Reference>\n"
                            . "				<ds:DigestMethod Algorithm=\"http://www.altova.com/\">\n"
                            . "				</ds:DigestMethod>\n"
                            . "				<ds:DigestValue>\n"
                            . "				</ds:DigestValue>\n"
                            . "			</ds:Reference>\n"
                            . "		</ds:SignedInfo>\n"
                            . "		<ds:SignatureValue>\n"
                            . "		</ds:SignatureValue>\n"
                            . "	</ds:Signature>"*/
                            . "</dte:GTDocumento>";

                //Validar XSD


           // p_log("<xmp lang='xml'>".$xml_fel."</xmp>");
            $respuesta->setResultado(true);
            $respuesta->setCantidadErrores(0);
            $respuesta->setDescripcion("XML Generado Correctamente.");
            $respuesta->setErrores(null);
            $respuesta->setXml($xml_fel);     
            return $respuesta;            


		} 

		if (get_class($transaccion)=="AnulacionFel")
		{
			 $anulacion_fel = $transaccion;
			 $FechaHoraAnulacion = $anulacion_fel->getFechaHoraAnulacion();
             $NITEmisor = $anulacion_fel->getNITEmisor();
             $FechaEmisionDocumentoAnular = $anulacion_fel->getFechaEmisionDocumentoAnular();
             $IDReceptor = $anulacion_fel->getIDReceptor();
             $NumeroDocumentoAAnular = $anulacion_fel->getNumeroDocumentoAnular();
             $MotivoAnulacion = $anulacion_fel->getMotivoAnulacion();

             $xml_anulacion_fel = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                            . "<dte:GTAnulacionDocumento xmlns:n1=\"http://www.altova.com/samplexml/other-namespace\" xmlns:dte=\"http://www.sat.gob.gt/dte/fel/0.1.0\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" Version=\"0.1\" xsi:schemaLocation=\"http://www.sat.gob.gt/dte/fel/0.1.0 C:\\Users\\User\\Desktop\\FEL\\Esquemas\\GT_AnulacionDocumento-0.1.0.xsd\">\n"
                            . "	<dte:SAT>\n"
                            . "		<dte:AnulacionDTE ID=\"DatosCertificados\">\n"
                            . "			<dte:DatosGenerales FechaHoraAnulacion=\"" . $FechaHoraAnulacion . "\" ID=\"DatosAnulacion\" NITEmisor=\"" . $NITEmisor . "\" FechaEmisionDocumentoAnular=\"" . $FechaEmisionDocumentoAnular . "\" IDReceptor=\"" . $IDReceptor . "\" NumeroDocumentoAAnular=\"" . $NumeroDocumentoAAnular . "\" MotivoAnulacion=\"" . $MotivoAnulacion . "\"/>\n"
                            . "		</dte:AnulacionDTE>\n"
                            . "	</dte:SAT>\n"
                            . "</dte:GTAnulacionDocumento>";

            $respuesta->setResultado(true);
      	    $respuesta->setCantidadErrores(0);
            $respuesta->setDescripcion("XML Generado Correctamente.");
            $respuesta->setErrores(null);
            $respuesta->setXml($xml_anulacion_fel);     

             return $respuesta;
		}
	}

	public function imprime_complementos($complemento, $posicion) {
        $tipo_complemento = get_class($complemento);
        $plantilla = "";
        $uri = "";
        $id_complemento = "";
        $nombre_complemento = "";

        switch ($tipo_complemento) {
            case "ComplementoExportacion":

                $exportacion = $complemento;

                $uri = $exportacion->getURIComplemento();
                $id_complemento = $exportacion->getIDComplemento();
                $nombre_complemento = $exportacion->getNombreComplemento();
                $direccion_consignatario = $exportacion->getDireccionConsignatarioODestinatario();
                $incoterm = $exportacion->getIncoterm();
                $nombre_consignatario = $exportacion->getNombreConsignatarioODestinatario();

                $etiqueta_codigo_comprador = "";
                if ($exportacion->getCodigoComprador() != NULL ) {
                    $etiqueta_codigo_comprador = "<cex:CodigoComprador>" . $exportacion->getCodigoComprador() . "</cex:CodigoComprador>\n";
                }

                $etiqueta_codigo_consignatorio_o_destinatario = "";
                if ($exportacion->getCodigoConsignatarioODestinatario() != NULL ) {
                    $etiqueta_codigo_consignatorio_o_destinatario = "<cex:CodigoConsignatarioODestinatario>" . $exportacion->getCodigoConsignatarioODestinatario() . "</cex:CodigoConsignatarioODestinatario>\n";
                }

                $etiqueta_codigo_exportador = "";
                if ($exportacion->getCodigoExportador() != NULL) {
                    $etiqueta_codigo_exportador = "<cex:CodigoExportador>" . $exportacion->getCodigoExportador() . "</cex:CodigoExportador>\n";
                }

                $etiqueta_direccion_comprador = "";
                if ($exportacion->getDireccionComprador() != NULL) {
                    $etiqueta_direccion_comprador = "<cex:DireccionComprador>" . $exportacion->getDireccionComprador() . "</cex:DireccionComprador>\n";
                }

                $etiqueta_otra_referencia = "";
                if ($exportacion->getOtraReferencia() != NULL) {
                    $etiqueta_otra_referencia = "<cex:OtraReferencia>" . $exportacion->getOtraReferencia() . "</cex:OtraReferencia>\n";
                }

                $etiqueta_nombre_comprador = "";
                if ($exportacion->getNombreComprador() != NULL) {
                    $etiqueta_nombre_comprador = "<cex:NombreComprador>" . $exportacion->getNombreComprador() . "</cex:NombreComprador>\n";
                }

                $etiqueta_nombre_exportador = "";
                if ($exportacion->getNombreExportador() != NULL) {
                    $etiqueta_nombre_exportador = "<cex:NombreExportador>" . $exportacion->getNombreExportador() . "</cex:NombreExportador>\n";
                }

                $plantilla_exportacion = "  <dte:Complemento IDComplemento=\"" . $id_complemento . "\" NombreComplemento=\"" . $nombre_complemento . "\" URIComplemento=\"" . $uri . "\">\n"
                        . "                <cex:Exportacion xmlns:cex=\"http://www.sat.gob.gt/face2/ComplementoExportaciones/0.1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" Version=\"1\" xsi:schemaLocation=\"http://www.sat.gob.gt/face2/ComplementoExportaciones/0.1.0 C:\\Users\\User\\Desktop\\FEL\\Esquemas\\GT_Complemento_Exportaciones-0.1.0.xsd\">\n"
                        . "	<cex:NombreConsignatarioODestinatario>" . $nombre_consignatario . "</cex:NombreConsignatarioODestinatario>\n"
                        . "	<cex:DireccionConsignatarioODestinatario>" . $direccion_consignatario . "</cex:DireccionConsignatarioODestinatario>\n"
                        . $etiqueta_codigo_consignatorio_o_destinatario
                        . $etiqueta_nombre_comprador
                        . $etiqueta_direccion_comprador
                        . $etiqueta_codigo_comprador
                        . $etiqueta_otra_referencia
                        . "	<cex:INCOTERM>" . $incoterm . "</cex:INCOTERM>\n"
                        . $etiqueta_nombre_exportador
                        . $etiqueta_codigo_exportador
                        . "</cex:Exportacion>\n"
                        . "\n"
                        . "			                \n"
                        . "                </dte:Complemento>";
                $plantilla = $plantilla_exportacion;
                break;
            case "ComplementoCambiaria":

                $cambiaria = $complemento;

                $uri = $cambiaria->getURIComplemento();
                $id_complemento = $cambiaria->getIDComplemento();
                $nombre_complemento = $cambiaria->getNombreComplemento();

                $plantilla_cambiaria = " 	<dte:Complemento IDComplemento=\"" . $id_complemento . "\" NombreComplemento=\"" . $nombre_complemento . "\" URIComplemento=\"" . $uri . "\">\n"
                        . "	<cfc:AbonosFacturaCambiaria xmlns:cfc=\"http://www.sat.gob.gt/dte/fel/CompCambiaria/0.1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" Version=\"1\" xsi:schemaLocation=\"http://www.sat.gob.gt/dte/fel/CompCambiaria/0.1.0 C:\\Users\\Nadir\\Desktop\\SAT_FEL_FINAL_V1\\Esquemas\\GT_Complemento_Cambiaria-0.1.0.xsd\">\n";

                // Inicia Parte 1 | Formacion dinamica del XML | Seccion de Abonos
               

                $cantidad_abonos =  count($cambiaria->getAbono());
                //var_dump($cantidad_abonos);
                if ($cantidad_abonos == 0) {
                    // No ejecuta ninguna accion, las etiquetas al estar vacias no apareceran
                } else {                    
                    foreach ($complemento->getAbono() as $abono) {

                        $plantilla_cambiaria = $plantilla_cambiaria
                                . "<cfc:Abono>\n"
                                . "<cfc:NumeroAbono>" . $abono->getNumeroAbono() . "</cfc:NumeroAbono>\n"
                                . "<cfc:FechaVencimiento>" . $abono->getFechaVencimiento() . "</cfc:FechaVencimiento>\n"
                                . "<cfc:MontoAbono>" . $abono->getMontoAbono() . "</cfc:MontoAbono>"
                                . "</cfc:Abono>";
                               /*  var_dump($abono->getNumeroAbono());
                                var_dump($abono->getFechaVencimiento());
                                var_dump($abono->getMontoAbono()); */
                    }

                }
                // Finaliza Parte 1 | Formacion dinamica del XML | Seccion de Abonos

                $plantilla_cambiaria = $plantilla_cambiaria . "	</cfc:AbonosFacturaCambiaria>\n"
                        . "	</dte:Complemento>";

                $plantilla = $plantilla_cambiaria;

                break;
            case "ComplementoEspecial":

                $especial = $complemento;
                
                
                $uri = $especial->getURIComplemento();
                $id_complemento = $especial->getIDComplemento();
                $nombre_complemento = $especial->getNombreComplemento();

                $retencion_isr = $especial->getRetencionISR();

                $etiqueta_retencion_iva = "";
                if ($especial->getRetencionIVA() != NULL) {
                    $etiqueta_retencion_iva = "<cfe:RetencionIVA>" . $especial->getRetencionIVA() . "</cfe:RetencionIVA>\n";
                    
                }

                $total_menos_retenciones = $especial->getTotalMenosRetenciones();

                $plantilla_especial = "<dte:Complemento IDComplemento=\"" . $id_complemento . "\" NombreComplemento=\"" . $nombre_complemento . "\" URIComplemento=\"" . $uri . "\">\n"
                        . "               <cfe:RetencionesFacturaEspecial Version=\"1\" xsi:schemaLocation=\"http://www.sat.gob.gt/face2/ComplementoFacturaEspecial/0.1.0 C:\\Users\\User\\Desktop\\FEL\\Esquemas\\GT_Complemento_Fac_Especial-0.1.0.xsd\" xmlns:cfe=\"http://www.sat.gob.gt/face2/ComplementoFacturaEspecial/0.1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n"
                        . "	<cfe:RetencionISR>" . $retencion_isr . "</cfe:RetencionISR>\n"
                        . $etiqueta_retencion_iva
                        . "	<cfe:TotalMenosRetenciones>" . $total_menos_retenciones . "</cfe:TotalMenosRetenciones>\n"
                        . "</cfe:RetencionesFacturaEspecial>\n"
                        . "\n"
                        . "                </dte:Complemento>";

                $plantilla = $plantilla_especial;

                break;
            case "ComplementoNotas":

                $notas = $complemento;
               
                $uri = $notas->getURIComplemento();
                $id_complemento = $notas->getIDComplemento();
                $nombre_complemento = $notas->getNombreComplemento();

                $fecha_doc_origen = $notas->getFechaEmisionDocumentoOrigen();
                $motivo_ajuste = $notas->getMotivoAjuste();
                $numero_autorizacion_doc_origen = $notas->getNumeroAutorizacionDocumentoOrigen();

                $etiqueta_numero_documento_origen = "";
                if ($notas->getNumeroDocumentoOrigen() != NULL) {
                    $etiqueta_numero_documento_origen = "NumeroDocumentoOrigen=\"" . $notas->getNumeroDocumentoOrigen() . "\"";
                }

                $etiqueta_regimen_antiguo = "";
                if ($notas->getRegimenAntiguo() != NULL) {
                    $etiqueta_regimen_antiguo = "RegimenAntiguo=\"" . $notas->getRegimenAntiguo() . "\"";
                }

                $etiqueta_serie_doc_origen = "";
                if ($notas->getSerieDocumentoOrigen() != NULL) {
                    $etiqueta_serie_doc_origen = "SerieDocumentoOrigen=\"" . $notas->getSerieDocumentoOrigen() . "\"";
                }

                $plantilla_notas = "<dte:Complemento IDComplemento=\"" . $id_complemento . "\" NombreComplemento=\"" . $nombre_complemento . "\" URIComplemento=\"" . $uri . "\">\n"
                        . "<cno:ReferenciasNota xmlns:cno=\"http://www.sat.gob.gt/face2/ComplementoReferenciaNota/0.1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" " . $etiqueta_regimen_antiguo . "  " . $etiqueta_numero_documento_origen . " Version=\"0.0\" MotivoAjuste=\"" . $motivo_ajuste . "\" FechaEmisionDocumentoOrigen=\"" . $fecha_doc_origen . "\" " . $etiqueta_serie_doc_origen . " NumeroAutorizacionDocumentoOrigen=\"" . $numero_autorizacion_doc_origen . "\" xsi:schemaLocation=\"http://www.sat.gob.gt/face2/ComplementoReferenciaNota/0.1.0 C:\\Users\\User\\Desktop\\FEL\\Esquemas\\GT_Complemento_Referencia_Nota-0.1.0.xsd\"/>\n"
                        . "</dte:Complemento>";

                $plantilla = $plantilla_notas;

                break;
            default:
                break;
        }

        return $plantilla;
        
    }
    
}

