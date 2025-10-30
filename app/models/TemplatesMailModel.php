<?php
// TemplatesMailModel.php

class TemplatesMailModel
{
    /**
     * Genera la plantilla HTML del ticket de confirmación
     *
     * @param array $data Datos necesarios para renderizar la plantilla
     * @return string HTML generado
     */

     private function getNumberPhoneEnterprise($empresa_data){
		$callings = '';
		if (isset($empresa_data["tel"]["en"]) && $empresa_data["tel"]["en"] !== null && $empresa_data["tel"]["en"] !== '' && isset($empresa_data["tel"]["es"]) && $empresa_data["tel"]["es"] !== null && $empresa_data["tel"]["es"] !== ''
		) {
			$callings .= '<div style="display:inline-block;width:49%;vertical-align:middle;color:#fff;font-size:0.9em;text-align:right;">&#9742; English: ' . $empresa_data["tel"]["en"] . '</div><div style="display:inline-block;vertical-align:middle;width:49%;color:#fff;font-size:0.9em;margin-left:2%;text-align:left;">&#9742; Español: ' . $empresa_data["tel"]["es"] . '</div>';
		} else {
			$callings .= $callings .= '<div></div>';
		}
		return $callings;
	}
    public function tiketConfirm(array $data): string
    {
        // Lista de empresas que no pagan dock fee
        $empresaSinNota = [100];

        // Inicializa dock fee si aplica
        $sinDockfeet = [
            'en' => '',
            'es' => ''
        ];
        $callings = $this->getNumberPhoneEnterprise($data);
        if (!in_array($data['empresa_id'], $empresaSinNota)) {
            $sinDockfeet = [
                'en' => '<strong style="color: #ea5b0c;">- Note: </strong> Remember surcharge is not included (' . number_format($data['dock_fee'], 2) . ' USD per person, to pay at check-in). <br />',
                'es' => '<strong style="color: #ea5b0c;">- Nota: </strong> Recuerde que el derecho de saneamiento ambiental no está incluido (' . number_format($data['dock_fee'], 2) . ' USD por persona, para pagar en el check-in). <br />'
            ];
        }

        // Textos base multilenguaje
        $texto = [
            'tittle' => [
                'en' => 'Receipt for your purchase with <a href="' . htmlspecialchars($data['website']) . '" style="color: #3299cb; text-decoration: none;" target="_blank">' . htmlspecialchars($data['webname']) . '</a>',
                'es' => 'Recibo por su compra con <a href="' . htmlspecialchars($data['website']) . '" style="color: #3299cb; text-decoration: none;" target="_blank">' . htmlspecialchars($data['webname']) . '</a>'
            ],
            'texto1' => [
                'en' => 'Please note that you will receive a separate email with a confirmation including date, pick up time and other important details.',
                'es' => 'Recibirá un correo electrónico por separado con la confirmación de su actividad, incluyendo la fecha, hora de recogida y otros detalles importantes.'
            ],
            'politica' => [
                'en' => '<p style="margin-top: 5px; margin-bottom: 5px;">' . $sinDockfeet['en'] . '<br />
                         <strong style="color: #ea5b0c;">- Reschedule policy: </strong> Rescheduling before 12 hours is available for free, but less than that will have a penalty of USD 20, payable at the port. <br /><br />
                         <strong style="color: #ea5b0c;">- Cancellation Policy: </strong> Canceling 24 hours in advance has a full refund. On the same date, missing the pick-up and having an illness without a medical receipt are not refundable. In case of bad weather conditions, we will provide the option to reschedule or get a full refund. This option wouldn\'t apply for previous no-shows.</p>',
                'es' => '<p style="margin-top: 5px; margin-bottom: 5px;">' . $sinDockfeet['es'] . '<br />
                         <strong style="color: #ea5b0c;">- Política de reprogramación: </strong> Reprogramar su actividad no tiene ningún costo, solicitando al menos 6 horas antes de su actividad. Reprogramar con menos de 6 horas tendrá una penalidad de $20 USD. <br /><br />
                         <strong style="color: #ea5b0c;">- Política de cancelación: </strong> Reembolso del 100% cancelando 24 horas por adelantado. Reembolso 50% cancelación 12-6 horas por adelantado. Cancelar con menos de 6 horas de anticipación o no tomar el tour se marcará como "No Show" y no se realizará reembolso alguno.</p>'
            ],
            'tabla1' => ['en' => 'Booking ID', 'es' => 'Booking ID'],
            'tabla2' => ['en' => 'Tour', 'es' => 'Tour'],
            'tabla3' => ['en' => 'Activity date', 'es' => 'Fecha de la actividad'],
            'tabla4' => ['en' => 'Activity time', 'es' => 'Horario de la actividad'],
            'tabla5' => ['en' => 'Addons', 'es' => 'Extras'],
            'tabla6' => ['en' => 'Tickets', 'es' => 'Tickets'],
            'tabla7' => ['en' => 'Pax', 'es' => 'Pax']
        ];

        // Mensaje y etiquetas según el estado
        // $salud = [
        //     'en' => 'your payment was successful!',
        //     'es' => '¡su pago fue exitoso!'
        // ];
        // $indTo = [
        //     'en' => "Total",
        //     'es' => "Total"
        // ];
        // $ext = [
        //     'en' => "",
        //     'es' => ""
        // ];

        // if ($data['estado'] === 3){
            $salud = [
                'en' => $data['cliente_name'] . ' your reservation has been successful!',
                'es' => '¡Su reserva fue exitosa!'
            ];
            $indTo = [
                'en' => "Balance due*",
                'es' => 'Balance por pagar*'
            ];
            $ext = [
                'en' => '<tr><td colspan="2" align="center"><p>Payable at check-in at the marina, either cash or card. Bring ID if paying with card.</p></td></tr>',
                'es' => '<tr><td colspan="2" align="center"><p>A pagar al momento del registro en la marina, en efectivo o tarjeta (llevar identificación oficial).</p></td></tr>'
            ];
        // }

        // Transaction info (si aplica)
        $tranas = [
            'en' => "",
            'es' => ""
        ];
        if (!empty($data['referencia'])) {
            $tranas = [
                "en" => '<tr><td style="color: #607D8B;"><b>Reference:</b></td><td>' . htmlspecialchars($data['referencia']) . '</td></tr>',
                "es" => '<tr><td style="color: #607D8B;"><b>Referencia:</b></td><td>' . htmlspecialchars($data['referencia']) . '</td></tr>'
            ];
        }

        // Addons (opcional)
        $addonsHtml = "";
        if (!empty($data['addons']) && is_array($data['addons'])) {
            $addonsHtml = '<tr><td>' . $texto['tabla5'][$data['leng']] . '</td><td>' . htmlspecialchars(implode(", ", $data['addons'])) . '</td></tr>';
        }
        $ticketsHtml = "";
        if (!empty($data['tickets']) && is_array($data['tickets'])) {
            $sanitizedTickets = array_map('htmlspecialchars', $data['tickets']);
            $ticketsHtml = '<tr><td>' . $texto['tabla6'][$data['leng']] . '</td><td>' . implode('<br>', $sanitizedTickets) . '</td></tr>';
        }

        // -------------------
        // 📦 Construir plantilla HTML
        // -------------------
        $html = '<table border="0" align="center" width="600" style="border-collapse: collapse;">
            <tr>
                <td colspan="2" style="border-bottom:2px solid #bbdefb;padding-bottom:10px; display:flex; align-items:center;">
                    <img src="' . htmlspecialchars($data['company_logo']) . '" alt="' . htmlspecialchars($data['webname']) . '" style="height:50px;">
                    <div style="display:inline-block; width:calc(100% - 105px); vertical-align:middle; font-weight:600;">
                        <h1 align="center" style="font-size:18px; color: #3299cb; margin-top:20px; margin-bottom:20px;">' . $texto['tittle'][$data['leng']] . '</h1>

                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding:10px 0;">
                    <p style="text-align: center; color:'. $data['primary_color'].';"><strong>' . $salud[$data['leng']] . '</strong></p>
                    <p>' . $texto['texto1'][$data['leng']] . '</p>
                    <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; margin-top:10px;">
                        <tr style="background-color:#f1f1f1;">
                            <th>' . $texto['tabla1'][$data['leng']] . '</th>
                            <th>' . htmlspecialchars($data['nog']) . '</th>
                        </tr>
                        <tr>
                            <td>' . $texto['tabla2'][$data['leng']] . '</td>
                            <td>' . htmlspecialchars($data['actividad']) . '</td>
                        </tr>
                        <tr>
                            <td>' . $texto['tabla3'][$data['leng']] . '</td>
                            <td>' . htmlspecialchars($data['datepicker']) . '</td>
                        </tr>
                        <tr>
                            <td>' . $texto['tabla4'][$data['leng']] . '</td>
                            <td>' . htmlspecialchars($data['time']) . '</td>
                        </tr>
                        ' . $addonsHtml . '
                        ' . $ticketsHtml . '
                        <tr>
                            <td>' . $texto['tabla7'][$data['leng']] . '</td>
                            <td>' . htmlspecialchars($data['pax']) . '</td>
                        </tr>
                        <tr>
                            <td><strong>' . $indTo[$data['leng']] . '</strong></td>
                            <td><strong>$' . number_format($data['total'], 2) . ' ' . $data['moneda'] . '</strong></td>
                        </tr>
                        ' . $tranas[$data['leng']] . '
                    </table>
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" style="margin-top:10px;">
                        ' . $ext[$data['leng']] . '
                    </table>
                    ' . $texto['politica'][$data['leng']] . '
                </td>
            </tr>
            <tr>
                <td style="background:#2196f3;padding-left:10px;padding-right:10px">
                    <div style="display:block;text-align:center;margin-top:6px">
                        <a href="' . htmlspecialchars($data['website']) . '" 
                           style="color:#fff;font-family:sans-serif;text-decoration:none;text-align:center">
                           <b style="font-size:1.1em">' . htmlspecialchars($data['webname']) . '</b>
                        </a>
                    </div>

                    ' . $callings . '

                    <div style="display:block;text-align:center;margin-top:5px">
                        ' . $data['social'] . '
                    </div>
                </td>
            </tr>
        </table>';

        return $html;
    }
    public function paymentRequest(array $data)
    {
        $price = $data['dock_fee'];
        $nota = '';
        $notaDock = '';

        $sinArrecifes = ['DARUINMAYAS','RUINMAYAS',"LBATVJT","LBHRJT","LBZATVJT","LBZHRT","LBHRAT","LBEAE","LBCABCEN","LBATVZC","LBATVHC","LBZHRCENOTE","LBZHATVC","CONVHRCZ","CONVATVCZ","WATVLB","HRCTCJLB","ZATVAJCLB","ZHCJCLB","HRATVJTCSLB","EATVZHLB"];
        $sinDockfee = ["LBATVJT","LBHRJT","LBZATVJT","LBZHRT","LBHRAT","LBEAE","LBCABCEN","LBATVZC","LBATVHC","LBZHRCENOTE","LBZHATVC","CONVHRCZ","CONVATVCZ","WATVLB","HRCTCJLB","ZATVAJCLB","ZHCJCLB","HRATVJTCSLB","EATVZHLB","CRMFLYANDRID","DWAVERUNNERSAPC","WAVERUNNERS"];

        $leng = $data['leng'];

        $texto = [
            'tittle' => ['en'=>'Payment Request','es'=>'Petición de Pago'],
            'saludo' => ['en'=>'Hi','es'=>'Hola'],
            'razon' => [
                'en' => $data['empresaname'] . ' sends you a request for payment',
                'es' => $data['empresaname'] . ' le ha enviado una petición para pago'
            ],
            'tabla1' => ['en'=>'Reference','es'=>'Referencia'],
            'tabla2' => ['en'=>'Activity date','es'=>'Fecha de actividad'],
            'horario'=>['en'=>'Schedule','es'=>'Horario de actividad'],
            'tabla4'=>['en'=>'Addons','es'=>'Extras'],
            'tabla5' => ['en' => 'Tickets', 'es' => 'Tickets'],
            'boton'=>['en'=>'Pay Now','es'=>'Pagar Ahora']
        ];

        $notaArrecifes = [
            'en'=>'Sunscreen is not allowed, as an environmental protection measure (coral reef damage). Long sleeve shirt or rashguard is recommended.<br /><br />',
            'es'=>'No se permite el uso de bloqueador solar como medida de protección ambiental (daño a los arrecifes de coral). Se recomienda usar camisa de manga larga o playera de licra.<br /><br />'
        ];

        $notaDockFee = [
            'en'=>"<strong style='color:#f44336;'>- Note: </strong> Remember surcharge is not included ({$price} usd per person, to pay at check-in).<br />",
            'es'=>"<strong style='color:#f44336;'>- Nota: </strong> Recuerda que el recargo no está incluido ({$price} usd por persona, a pagar al hacer check-in).<br />"
        ];

        if(!in_array($data['codetour'],$sinArrecifes)) $nota = $notaArrecifes[$leng];
        if(!in_array($data['codetour'],$sinDockfee)) $notaDock = $notaDockFee[$leng];

        $policies = [
            'en'=>$notaDock.'<br />'.$nota."<strong style='color:#f44336;'>- Reschedule policy: </strong> Rescheduling before 12 hours is available for free, but less than that will have a penalty of USD 20, payable at the port.<br /><br /><strong style='color:#f44336;'>- Cancellation Policy: </strong> Canceling 24 hours in advance has a full refund. On the same date, missing the pick-up and having an illness without a medical receipt are not refundable. In case of bad weather conditions, we’ll provide you the option of reschedule or get a full refund. This option wouldn’t be applicable in case of being a reschedule for a previous no-show.",
            'es'=>$notaDock.'<br />'.$nota."<strong style='color:#f44336;'>- Política de Reprogramación: </strong> Reprogramar con al menos 12 horas de anticipación es gratis. Después de ese tiempo, se aplicará una penalización de 20 USD, pagadera en el puerto.<br /><br /><strong style='color:#f44336;'>- Política de Cancelación: </strong> Cancelando con 24 horas de anticipación se ofrece reembolso completo. El mismo día, si no te presentas o tienes una enfermedad sin justificante médico, no es reembolsable. En caso de mal clima, te ofreceremos reprogramar o reembolso total. Esta opción no aplica si la reprogramación es de un no-show anterior."
        ];

        $callings = "<div style='display:inline-block;width:49%;vertical-align:middle;color:#fff;font-size:0.9em; text-align: center;'>&#9742; English: {$data['tel']['en']}</div><div style='display:inline-block;vertical-align:middle;width:49%;color:#fff;font-size:0.9em;margin-left:2%;text-align:center;'>&#9742; Español: {$data['tel']['es']}</div>";

        $addons = is_array($data['addons']) ? implode(", ", $data['addons']) : ($data['addons'] ?? '');
        $referencia = !empty($data['referencia']) ?? '';
        $social = $data['social'] ?? '';
        $cliente_name = $data['cliente_name'] ?? '';
        $addonsHtml = "";
        if (!empty($data['addons']) && is_array($data['addons'])) {
            $addonsHtml = '<tr><td>' . $texto['tabla4'][$data['leng']] . '</td><td>' . htmlspecialchars(implode(", ", $data['addons'])) . '</td></tr>';
        }
        $ticketsHtml = "";
        if (!empty($data['tickets']) && is_array($data['tickets'])) {
            $sanitizedTickets = array_map('htmlspecialchars', $data['tickets']);
            $ticketsHtml = '<tr><td>' . $texto['tabla5'][$data['leng']] . '</td><td>' . implode('<br>', $sanitizedTickets) . '</td></tr>';
        }
        $message = "
        <table border='0' align='center' width='600' style='border-collapse:collapse;background:#fff;border:1px solid #E0E0E0;'>
            <tbody>
                <tr style='background: {$data['primary_color']};'>
                    <td colspan='2' style='display:flex;align-items:center; gap: 20px;'>
                        <img src='{$data['company_logo']}' alt='{$data['empresaname']}' style='height:50px; width: 110px; object-fit: contain;'>
                        <h1 style='font-size:25px;color:#fff;font-family:sans-serif; margin-bottom: 0 !important;'>{$texto['tittle'][$leng]}</h1>
                    </td>
                </tr>
                <tr><td colspan='2' style='padding:8px;'>
                    <h1 align='center' style='font-size:20px;color:#212121;font-weight:100;'>{$texto['saludo'][$leng]} {$cliente_name}</h1>
                    <h2 align='center' style='color:#37474F;font-size:17px;font-weight:400;border-bottom:1px dotted #424242;'>{$texto['razon'][$leng]}</h2>
                    <table cellpadding='5' border='0' style='border-collapse:collapse;font-size:15px;width:100%;'>
                        <tr><td colspan='2'><p style='font-size:1.1rem;color:#122b53;'>{$data['actividad']}</p></td></tr>
                        <tr><td style='color:#122b53;'>{$texto['tabla1'][$leng]}</td><td>{$data['referencia']}</td></tr>
                        <tr><td style='color:#122b53;'>{$texto['tabla2'][$leng]}</td><td>{$data['datepicker']}</td></tr>
                        <tr><td style='color:#122b53;'>{$texto['horario'][$leng]}</td><td>{$data['time']}</td></tr>
                        {$addonsHtml}  <!-- fila de addons -->
                        {$ticketsHtml} <!-- fila de tickets -->
                        <tr><td style='color:#122b53;'>Pax</td><td>{$data['pax']}</td></tr>
                        
                        <tr style='border-top:1px dotted #9E9E9E;'><td style='color:#122b53;font-size:1.3rem;'>Total:</td><td style='font-size:1.3rem;'>$ {$data['total']} {$data['moneda']}</td></tr>
                    </table>
                    <div style='text-align:center;margin-top:20px;'>
                        <a href='{$data['website']}/payment-request/?utm_nooverride=1&meth={$data['metodopago']}&reference={$referencia}' style='font-size:1rem;color:#fff;padding:8px 20px;text-decoration:none;background:{$data['primary_color']};font-weight:500;display:inline-block;'>{$texto['boton'][$leng]}</a>
                    </div>
                </td></tr>
                <tr><td style='background:{$data['primary_color']};padding:10px;color:#fff;'>{$policies[$leng]}</td></tr>
                <tr>
                    <td style='background:{$data['primary_color']};padding:10px;'>
                        <div style='text-align:center;'><a href='{$data['website']}' style='color:#fff;text-decoration:none;font-size:1.1em;'>{$data['webname']}</a></div>
                        {$callings}
                        <div style='text-align:center;margin-top:5px;'>{$social}</div>
                    </td>
                </tr>
            </tbody>
        </table>";
        
        return $message;
        
    }

    public function procesarReserva($data)
	{
		$price = $data['dock_fee'];
		$fecha = $data['datepicker'];
		$nota = '';
		$notaDock = '';
		$sinArrecifes = ['DARUINMAYAS', 'RUINMAYAS', "LBATVJT", "LBHRJT", "LBZATVJT", "LBZHRT", "LBHRAT", "LBEAE", "LBCABCEN", "LBATVZC", "LBATVHC", "LBZHRCENOTE", "LBZHATVC", "CONVHRCZ", "CONVATVCZ", "WATVLB", "HRCTCJLB", "ZATVAJCLB", "ZHCJCLB", "HRATVJTCSLB", "EATVZHLB"];
		$sinDockfeet = ["LBATVJT", "LBHRJT", "LBZATVJT", "LBZHRT", "LBHRAT", "LBEAE", "LBCABCEN", "LBATVZC", "LBATVHC", "LBZHRCENOTE", "LBZHATVC", "CONVHRCZ", "CONVATVCZ", "WATVLB", "HRCTCJLB", "ZATVAJCLB", "ZHCJCLB", "HRATVJTCSLB", "EATVZHLB", "CRMFLYANDRID", "DWAVERUNNERSAPC", "WAVERUNNERS"];
		$showID = array(
			'en' => '<tr>
				<td style="font-size:14px; ">
					<p style="margin-top:5px;margin-bottom:5px;">As a security measure for all of our clients and our company too, we\'ve started asking to <span style="color:#F44336">show a valid ID that matches with the name of the account holder</span>.<br><br> The only purpouse of this is to make sure we\'re giving the service to the right person.<br><br> In case the account holder is not part of the people taking the tour, you can send an image in advance via email.<br><br> You\'ll be asked for your ID on ' . $fecha . ' before taking ' . $data['actividad'] . ' activity. <span style="color:#F44336">In case you don\'t show it, we might deny the service if considered and there won\'t be either cancellations or refunds</span>.<br><br> We remain at your disposal for any doubt or comment.<br><br>Best regards.
					</p>
				</td>
			</tr>',
			'es' => '<tr>
				<td style="font-size:14px; ">
					<p style="margin-top:5px;margin-bottom:5px;">Como medida de seguridad para todos nuestros clientes y para la empresa, hemos comenzado a solicitar <span style="color:#F44336">mostrar identificación oficial que corresponda con el titular de la cuenta con la que se realizó la reservación de su actividad</span>.<br><br> El único propósito es asegurarnos que estamos brindando el servicio a la persona correcta.<br><br> En caso que el titular de la cuenta, no sea parte de la(s) persona(s) que disfrutaran de la actividad, su identificación podrá ser enviada vía correo electrónica con anticipación.<br><br> La identificación le será requerida por única vez el día ' . $fecha . ' antes de tomar la actividad ' . $data['actividad'] . '. <span style="color:#F44336">En caso de no cumplir con este requisito, el servicio le podrá ser negado si así se considera, y ningún reembolso o cancelación será otorgado.<br><br> Quedamos abiertos para cualquier duda o comentario.</span><br><br>Saludos.
					</p>
				</td>
			</tr>'
		);
		$notaDockfee = array('en' => '<strong style="color: #f44336;">- Note: </strong> Remember surcharge of is not included (' . $price . ' usd per person, to pay at check-in). <br />', 'es' => '<strong style="color: #f44336;">- Nota: </strong> Recuerde que el derecho de saneamiento ambiental no esta incluido (' . $price . ' usd por persona, para pagar en el check-in) <br />');
		$notaArrecifes = array('en' => 'Sunscreen is not allowed, as an enviromental protection measure (coral reef damage). Long sleeve shirt or rashguard is recommended. <br /><br />', 'es' => 'No se permite el protector solar, como medida de protección ambiental (daño a los arrecifes de coral). Se recomienda una camisa de manga larga o una camiseta de surf.<br /><br /> ');
		if (!in_array($data['codetour'], $sinArrecifes)) {
			$nota = $notaArrecifes[$data['leng']];
		}
		if (!in_array($data['codetour'], $sinDockfeet)) {
			$notaDock = $notaDockfee[$data['leng']];
		}
		

		$texto = array(
			'texto1' => array(
				'en' => '<tr>
						<td style="font-size:14px;">
							<p style="margin-top:5px;margin-bottom:5px;line-height:20px;">The only reason for this email is to agree your pick up time. Our driver will pick you up at <b>' . $data['dataMail']['pickup_lugar']. ' </b>. Please plan to be on time since ' . "there's only 5 minutes tolerance on transportation." . '</p>
						</td>
					</tr>
					<tr>
						<td>
							Pick up time: <span style="color:#ec008c;margin-bottom:5px;margin-top:5px;font-size: 1.5em;">' . $data['dataMail']['pickup_horario'] . '</span>
						</td>
					</tr>',
				'es' => '<tr>
					<td style="font-size:14px;">
						<p style="margin-top:5px;margin-bottom:5px;line-height:20px;">Sólo le queremos informar su horario de recogida. Nuestro conductor le recogerá en el <b>' . $data['dataMail']['pickup_lugar'] . ' </b>. Por favor tome en cuenta estar a tiempo pues solo hay 5 minutos de tolerancia en transportación.</p>
					</td>
				</tr>
				<tr>
					<td>
						Horario de transportación: <span style="color:#ec008c;margin-bottom:5px;margin-top:5px;font-size: 1.5em;">' . $data['dataMail']['pickup_horario']. '</span>
					</td>
				</tr>'
			),
			'texto2' => array(
				'en' => '<tr>
					 	<td style="font-size:14px;">
					 		<p style="margin-top:5px;margin-bottom:5px;margin-bottom: 15px;"><b style="color:#000;">Transportation issues phone support:</b> <span style="color:red;">998 535 7996<span> (Mobile phone). Call us  in case of any issue or delay in transportation on the day of your activity, we\'ll gladly solve anything within minutes.
					 		</p>
					 	</td>
					</tr>',
				'es' => '<tr>
				 		<td style="font-size:14px; ">
				 			<p style="margin-top:5px;margin-bottom:5px;margin-bottom: 15px;"><b style="color:#000; margin-bottom:2px; margin-top:2px;">Soporte telefónico para transporte:</b> <span style="color:red;">998 535 7996<span>  (teléfono móvil- celular), llámenos en caso de cualquier problema o retraso en el transporte el día de su actividad, con gusto resolveremos su problema en minutos.</p>
				 		</td>
				 	</tr>'
			),
			'texto3' => array(
				'en' => '<tr>
						<td colspan="2" style="margin-top:5px;margin-bottom:5px; font-size: 14px; ">
							<p><b>Important:</b> In case you miss the shuttle you can reach our location to enjoy your tour at ' . $data["address"]["name"] . '. ' . $data["address"]["addr"] . ' :</p>
						</td>
					</tr>',
				'es' => '<tr>
						<td colspan="2" style=" font-size: 14px; ">
							<p style="margin-top:5px;margin-bottom:5px"><b>Importante:</b> En caso de que pierda la transportación, usted podrá llegar a nuestra ubicación para disfrutar de su tour: ' . $data["address"]["name"] . '. ' . $data["address"]["addr"] . ' :</p>
						</td>
					</tr>'
			),
			'texto4' => array(
				'en' => '<tr>
						<td colspan="2" style=" font-size: 14px;">
							<p style="margin-top:5px;margin-bottom:5px;"><b style="color: #F44336;">Note: </b> Please be free to arrive to ' . $data["address"]["name"] . '. ' . $data["address"]["addr"] . '.</p>
						</td>
					</tr>',
				'es' => '<tr>
						<td colspan="2" style=" font-size: 14px;">
							<p style="margin-top:5px;margin-bottom:5px;"><b style="color: #F44336;">Nota: </b>Tenga la libertad de llegar a ' . $data["address"]["name"] . ', nuestra dirección es ' . $data["address"]["addr"] . ', donde le recibiremos gustosamente, esperamos verlo pronto, gracias.</p>
						</td>
					</tr>'
			),
			'politica' => array('en' => '<p style="margin-top: 5px; margin-bottom: 5px;">' . $notaDock . '<br />' . $nota . '<strong style="color: #f44336;">- Reschedule policy: </strong> Rescheduling before 12 hours is available for free, but less than that will have a penalty of USD 20, payable at the port. <br /><br /> <strong style="color: #f44336;">- Cancellation Policy: </strong> Canceling 24 hours in advance has a full refund. On the same date, missing the pick-up and having an illness without a medical receipt are not refundable. In case of bad weather conditions,  well provide you the option of reschedule or get a full refund. This option wouldnt be applicable in case of being a reschedule for a previous no-show.</p>', 'es' => '<p style="margin-top: 5px; margin-bottom: 5px;">' . $notaDock . '<br />' . $nota . ' <strong style="color: #f44336;">- Política de reprogramación: </strong> Reprogramar su actividad no tiene ningún costo, solicitando al menos 6 horas antes de su actividad. Reprogramar con menos de 6 horas tendrá una penalidad de $20 usd. <br /><br /> <strong style="color: #f44336;">- Pol&iacute;tica de cancelaci&oacute;n: </strong>Reembolso del 100% cancelando 24 horas por adelantado. Reembolso 50% cancelaci&oacute;n12-6 horas por adelantado. Cancelar con menos de 6 horas de anticipaci&oacute;n o no tomar el tour se marcar&aacute; como "No Show" y no se realizar&aacute; reembolso alguno.</p>'),
			'tabla1' => array('en' => 'Reference', 'es' => 'Referencia'),
			'tabla2' => array('en' => 'Activity date', 'es' => 'Día de Actividad'),
			'tabla3' => array('en' => 'Activity time', 'es' => 'Hora de actividad'),
			'tabla4' => array('en' => 'Addons', 'es' => 'Extras'),
            'tabla5' => array('en' => 'Tickets', 'es' => 'Tickets'),
		);

		$header = array(
			'en' => '<tr style="border-bottom: 1px solid #E2E2E2;">
				<td style="display:flex;align-items:center;">
					<img src="' . $data["company_logo"] . '" alt="' . $data["empresaname"] . '"  style="height:50px; width: 120px; object-fit: contain;">
					<h1 style="font-weight:500;font-size:20px;color:#3299cb;font-family:sans-serif;padding:8px;display:inline-block;vertical-align:middle;width:calc(100% - 106px);text-align:center;">
						Your booking for ' . $data['actividad'] . ' is already confirmed.
					</h1>
				</td>
			</tr>',
			'es' => '<tr style="border-bottom: 1px solid #E2E2E2;">
				<td style="display:flex;align-items:center;">
					<img src="' . $data["company_logo"] . '" alt="' . $data["empresaname"] . '" style="height:50px; width: 120px; object-fit: contain;">
					<h1 style="font-weight:500;font-size:20px;color:#3299cb;font-family:sans-serif;padding:8px;display:inline-block;vertical-align:middle;width:calc(100% - 106px);text-align:center;">
						Su resevación para ' . $data['actividad'] . ' ha sido confirmada.
					</h1>
				</td>
			</tr>'
		);
		$saludo = array('es' => 'Hola', 'en' => 'Hi');
		$comentario = '<tr><td colspan="2" style="padding:0px;"><p>Nota: ' . $data['dataMail']['comentario'] . '</p></td></tr>';
		$message = '<table border="0" align="center" width="600px" style="border-collapse: collapse; border-spacing: initial;">';
		$message .= $header[$data['leng']];
		$message .= '<tr><td><h1 align="center" style="font-size: 20px; color:deeppink;margin-top:10px;">' . $saludo[$data['leng']] . ' ' . $data['cliente_name'] . '</h1></td></tr>';

		if ($data['dataMail']['tipo'] == 'transporte') {
			$message .= $texto['texto1'][$data['leng']];
		}
		// if ($data['mostrarID'] == 1) {
		// 	$message .= $showID[$data['leng']];
		// }
		$message .= $comentario;
        $addonsHtml = "";
        if (!empty($data['addons']) && is_array($data['addons'])) {
            $addonsHtml = '<tr><td>' . $texto['tabla4'][$data['leng']] . '</td><td>' . htmlspecialchars(implode(", ", $data['addons'])) . '</td></tr>';
        }
        $ticketsHtml = "";
        if (!empty($data['tickets']) && is_array($data['tickets'])) {
            $sanitizedTickets = array_map('htmlspecialchars', $data['tickets']);
            $ticketsHtml = '<tr><td>' . $texto['tabla5'][$data['leng']] . '</td><td>' . implode('<br>', $sanitizedTickets) . '</td></tr>';
        }
		$message .= '<tr>
			<td colspan="2" align="center">
				<table cellpadding="0" border="0" style="border-collapse: collapse;font-size: 15px;width:100%;">
						<tbody>
							<tr>
								<td style="color: #122b53;width: 55%;padding-left: 15px;"><p>' . $texto['tabla1'][$data['leng']] . '</p></td>
								<td>' . $data['referencia'] . '</td>
							</tr>
							<tr>
								<td style="color: #122b53;padding-left: 15px;"><p>' . $texto['tabla2'][$data['leng']] . '</p></td>
								<td>' . $fecha . '</td>
							</tr>	
							<tr>
								<td style="color: #122b53;padding-left: 15px;"><p>' . $texto['tabla3'][$data['leng']] . '</p></td>
								<td>' . $data['time'] . '</td>
							</tr>
							' .  $ticketsHtml . $addonsHtml . '
							<tr>
								<td style="color: #122b53;padding-left: 15px;"><p>Pax: </p></td>
								<td>' . $data['pax'] . '</td>
							</tr>
							<tr style="border-top: 1px solid #9E9E9E;border-top-style: dotted;">
								<td style="color: #122b53;"><p style="margin-top:5px;margin-bottom:5px;font-size: 1.3rem;">Total:</p></td>
								<td style="font-size: 1.3rem;">$ ' . $data['total'] . ' USD</td>
							</tr>
							<tr>
								<td align="center" colspan="2"><img src="https://www.totalsnorkelcancun.com/qrcode/' . $data['referencia'] . '.png" alt="' . $data['referencia'] . '"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>';

		if ($data['dataMail']['tipo'] == 'transporte') {
			$message .= $texto['texto2'][$data['leng']];
			$message .= $texto['texto3'][$data['leng']];
		} else {
			$message .= $texto['texto4'][$data['leng']];
		}
		$callings = $this->getNumberPhoneEnterprise($data);
		$message .= '<tr>
					<td align="center" colspan="2" style=" font-size: 14px;">
						<a href="' . $data["address"]["url"] . '" ><img src="' . $data["address"]["map"] . '" alt="' . $data["address"]["name"] . ' location" width="80%" height="auto">
						</a>
					</td>
				</tr>
				<tr>
					<td colspan="2" style=" font-size: 14px;">'
			. $texto['politica'][$data['leng']]
			. '</td>
				</tr>
				<tr>
		         	<td style="background:#2196f3;padding-left:10px;padding-right:10px">
		            	<div style="display:block;text-align:center;margin-top:6px">
							<a href="' . $data["website"] . '" style="color:#fff;font-family:sans-serif;text-decoration:none;text-align:center"><b style="font-size:1.1em">' . $data["webname"] . '</b></a>
						</div>
						<!--div style="display:inline-block;width:49%;vertical-align:middle;color:#fff;font-size:0.9em;text-align:right;">&#9742; English: ' . $data["tel"]["en"] . '</div><div style="display:inline-block;vertical-align:middle;width:49%;color:#fff;font-size:0.9em;margin-left:2%;text-align:left;">&#9742; Español: ' . $data["tel"]["es"] . '</div-->
						' . $callings . '
						<div style="display:block;text-align:center;margin-top:5px">'
			. $data["social"] .
			'</div>
					</td>
				</tr>
				
			</table>';
		return $message;
	}
    public function mailReproIngFromBooking($data)
    {

        $texto = array(
            'saludo' => array('en' => 'Dear', 'es' => 'Estimado'),
            'tittle' => array(
                'en' => 'You\'ve successfully changed your reservation! Below are the adjusted dates and times for',
                'es' => 'Has cambiado la reserva con éxito! A continuación se muestran las fechas y horas ajustadas para'
            ),
            'data1' => array('en' => 'Activity date', 'es' => 'Día de Actividad'),
            'data2' => array('en' => 'Start time', 'es' => 'Hora de inicio'),
            'data3' => array('en' => 'Please plan to arrive 10 minutes before', 'es' => 'Por favor planee llegar 10 minutos antes'),
            'data4' => array('en' => 'Meeting location', 'es' => 'Lugar para abordar el transporte'),
            'data5' => array(
                'en' => 'If you have any question feel free to call <b>' . $data["tel"]["en"] . '</b> or email <b>' . $data["questions_mail"] . '</b>. <p style="margin-top:5px;margin-bottom:5px;"><strong style="color:#f44336;">- Note:</strong> Dock fee not included (' . $data["dock_fee"] . ' usd per person).<br><br><strong style="color:#f44336;">- Reschedule policy:</strong> Free before 12 hours, less than that USD 20 penalty.<br><br><strong style="color:#f44336;">- Cancellation policy:</strong> 24h advance full refund.</p>',
                'es' => 'Si tiene alguna pregunta no dude en llamar a <b>' . $data["tel"]["es"] . '</b> o por correo electrónico a <b>' . $data["questions_mail"] . '</b>. <p style="margin-top:5px;margin-bottom:5px;"><strong style="color:#f44336;">- Nota:</strong> Derecho de saneamiento no incluido (' . $data["dock_fee"] . ' usd por persona).<br><br><strong style="color:#f44336;">- Política de reprogramación:</strong> Gratis si es con 12h de anticipación, menos de eso penalidad de $20.<br><br><strong style="color:#f44336;">- Política de cancelación:</strong> 24h de anticipación reembolso total.</p>'
            ),
            'data6' => array('en' => 'We\'re excited to see you soon!', 'es' => '¡Estamos muy contentos de verte pronto!')
        );

        $callings = $this->getNumberPhoneEnterprise([
            'tel' => $data['tel'],
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color']
        ]);

        $message = '<table border="0" align="center" width="700px" style="border-collapse:collapse;">
            <tbody>
                <tr>
                    <td style="display:flex;justify-content:space-between;align-items:center;width:100%;">
                        <img src="' . $data["company_logo"] . '" alt="' . $data["empresaname"] . '" style="height:50px;">
                        <img src="https://www.totalsnorkelcancun.com/img/calendario-reload.png" alt="img changed" style="height:45px;padding:10px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:8px;">
                        <table style="width:100%;" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td><h1 style="font-size:23px;color:#37474F;">' . $texto['saludo'][$data['leng']] . ' ' . $data["cliente_name"] . '</h1></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;">
                                        <p>'
                                        . $texto['tittle'][$data['leng']] . ': <b>' . $data["actividad"] . '</b><br><br>
                                        - ' . $texto['data1'][$data['leng']] . ': <b>' . $data["datepicker"] . '</b><br>
                                        - ' . $texto['data2'][$data['leng']] . ': <b>' . $data["time"] . '</b><br>
                                        - ' . $texto['data3'][$data['leng']] . '<br>
                                        - ' . $texto['data4'][$data['leng']] . ': <b>' . $data["hotel"] . '</b><br><br><br>'
                                        . $texto['data5'][$data['leng']]
                                        . '<br><br>'
                                        . $texto['data6'][$data['leng']]
                                        . '</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:' . $data["primary_color"] . ';padding:10px;">
                        <div style="text-align:center;margin-top:6px;">
                            <a href="' . $data["website"] . '" style="color:#fff;font-family:sans-serif;text-decoration:none;"><b style="font-size:1.1em">' . $data["webname"] . '</b></a>
                        </div>
                        ' . $callings . '
                        <div style="text-align:center;margin-top:5px;">
                            ' . $data["social"] . '
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>';

        return $message;
    }
    public function mailCancelacionFromBooking($data)
    {
        $texto = [
            'saludo' => ['en' => 'Hi', 'es' => 'Hola'],
            'tittle' => ['en' => "You've successfully cancelled your reservation", 'es' => 'Has cancelado su reserva'],
            'texto1' => [
                'en' => "You'll have your money back in 1-10 days according to your bank. <p style='margin-top:5px;margin-bottom:5px;'><strong style='color:#f44336;'>- Note:</strong> Dock fee not included ({$data['dock_fee']} usd per person).<br><br><strong style='color:#f44336;'>- Reschedule policy:</strong> Free before 12 hours, less than that USD 20 penalty.<br><br><strong style='color:#f44336;'>- Cancellation policy:</strong> 24h advance full refund...</p>",
                'es' => "Usted tendrá su dinero en 1-10 días de acuerdo a su banco. <p style='margin-top:5px;margin-bottom:5px;'><strong style='color:#f44336;'>- Nota:</strong> Derecho de saneamiento no incluido ({$data['dock_fee']} usd por persona).<br><br><strong style='color:#f44336;'>- Política de reprogramación:</strong> Gratis si es con 12h de anticipación, menos de eso penalidad de $20.<br><br><strong style='color:#f44336;'>- Política de cancelación:</strong> 24h de anticipación reembolso total...</p>"
            ],
            'contacto' => [
                'en' => "If you have any question feel free to call <b>{$data['tel']['en']}</b> or email <b>{$data['email']}</b>.",
                'es' => "Si tiene alguna pregunta no dude en llamar a <b>{$data['tel']['es']}</b> o por correo electrónico a <b>{$data['email']}</b>."
            ],
            'sentimiento' => ['en' => "Sorry you can't make it this time, but we hope to see you again soon!", 'es' => 'Sentimos que no pueda disfrutarlo esta vez, pero esperamos verle de nuevo pronto!'],
            'typeCancel' => "{$data['dataCancellation']['typeCancellation'][$data['leng']]}",
            'typeDescription' => "{$data['dataCancellation']['typeDescription'][$data['leng']]}",
            'categoryCancel' => "{$data['dataCancellation']['categoryCancellation'][$data['leng']]}",
            'categoryDescription' => "{$data['dataCancellation']['categoryDescription'][$data['leng']]}",
        ];
    
        // Convertir social a string si es array
        $social = is_array($data['social']) ? implode('', $data['social']) : $data['social'];
    
        $callings = $this->getNumberPhoneEnterprise([
            'tel' => $data['tel'],
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color']
        ]);
    
        $message = "<table border='0' align='center' style='border-collapse:collapse;'>
            <tbody>
                <tr>
                    <td align='left' style='padding:10px;display:flex;align-items:center;'>
                        <img src='{$data['company_logo']}' alt='{$data['empresaname']}' style='height:50px;'>
                        <img src='https://www.totalsnorkelcancun.com/img/tag_cancel.png' alt='img cancel' style='height:106px;float:right;padding:10px;'>
                    </td>
                </tr>
                <tr>
                    <td colspan='2' style='padding:8px;'>
                        <h1 style='font-size:23px;color:#37474F;'>{$texto['saludo'][$data['leng']]} {$data['cliente_name']}</h1>
                    </td>
                </tr>
                <tr>
                    <td style='padding:10px;'>
                        <p>{$texto['tittle'][$data['leng']]} <b>{$data['actividad']}</b><br><br>
                        {$texto['texto1'][$data['leng']]}<br><br>
                        {$texto['contacto'][$data['leng']]}<br><br>
                        {$texto['sentimiento'][$data['leng']]}<br><br>
                        <strong style='color:#f44336;'>{$texto['typeCancel']}</strong><br>
                        {$texto['typeDescription']}<br><br>
                        <strong style='color:#f44336;'>{$texto['categoryCancel']}</strong><br>
                        {$texto['categoryDescription']}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style='background:{$data['primary_color']};padding:10px;'>
                        <div style='text-align:center;margin-top:6px;'>
                            <a href='{$data['website']}' style='color:#fff;font-family:sans-serif;text-decoration:none;'>
                                <b style='font-size:1.1em'>{$data['webname']}</b>
                            </a>
                        </div>
                        {$callings}
                        <div style='text-align:center;margin-top:5px;'>
                            {$social}
                        </div>
                    </td>
                </tr>
                
            </tbody>
        </table>";
    
        return $message;
    }
    
}
