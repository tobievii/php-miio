<?php
/* 
*	Класс для работы с wifi-лампами Philips Light Bulb по протоколу miIO.
*
*	Copyright (C) 2017 Agaphonov Dmitri aka skysilver [mailto:skysilver.da@gmail.com]
*
*/

require('miio.class.php');

class philipsBulb {
	
	public 	$ip = '';
	public 	$token = '';
	public 	$debug = '';
	public 	$error = '';
	
	public 	$status = array('power' => '',
							'bright' => '',
							'cct' => '',
							'snm' => '',
							'dv' => '');
	
	public 	$dev = NULL;
	
	public function __construct($ip = NULL, $bind_ip = NULL, $token = NULL, $debug = false) {
		
		$this->ip = $ip;
		$this->token = $token;
		$this->debug = $debug;
		
		if ($bind_ip != NULL) $this->bind_ip = $bind_ip;
		 else $this->bind_ip = '0.0.0.0';
		
		$this->dev = new miIO($this->ip, $this->bind_ip, $this->token, $this->debug);
		
	}

	/*
		Получить расширенные сведения
	*/
	
	public function getInfo() {
	
		if ($this->dev->getInfo()) return $this->dev->data;
		 else return false;
	
	}
	
	/*
		Получить текущий статус:
			power - питание (on или off), 
			bright - яркость (от 1 до 100), 
			cct - цветовая температура (от 1 до 100), 
			snm - номер сцены (от 1 до 4),
			dv - таймер на выключение, макс. 6 часов (в секундах от 0 до 21600)
	*/
	
	public function getStatus() {
	
		$result = $this->dev->msgSendRcv('get_prop', '["power","bright","cct","snm","dv"]');
		
		if ($result) {
			if ($this->dev->data != '') {
				$res = json_decode($this->dev->data);
				if (isset($res->{'result'})) {
					$i = 0;
					foreach($this->status as $key => $value) { 
						$this->status[$key] = $res->{'result'}[$i];
						$i++;
					} 
					return true;
				} else if (isset($res->{'error'})) {
					$this->error = $res->{'error'}->{'message'};
					return false;
				}
			} else {
				$this->error = 'Нет данных';
				return false;
			}
		} else {
			$this->error = 'Ответ не получен';
			return false;
		}
		
	}
	
	/*
		Включить
	*/
	
	public function powerOn() {
	
		$result = $this->dev->msgSendRcv('set_power', '["on"]');
		return $this->verify($result);

	}
	
	/*
		Выключить
	*/
	
	public function powerOff() {
	
		$result = $this->dev->msgSendRcv('set_power', '["off"]');
		return $this->verify($result);
	
	}
	
	/*
		Установка яркости
	*/
	
	public function setBrightness($level = 50) {
	
		if ( ($level < 1) or ($level > 100) ) $level = 50;
		$result = $this->dev->msgSendRcv('set_bright', "[$level]");
		return $this->verify($result);
	
	}
	
	/*
		Установка цветовой температуры
	*/
	
	public function setColorTemperature($level = 50) {
	
		if ( ($level < 1) or ($level > 100) ) $level = 50;
		$result = $this->dev->msgSendRcv('set_cct', "[$level]");
		return $this->verify($result);
	
	}
	
	/*
		Переключение сцен - ярко, ТВ, тепло, полноч.
	*/
	
	public function setScene($num = 1) {
	
		if ( ($num < 1) or ($num > 4) ) $num = 1;
		$result = $this->dev->msgSendRcv('apply_fixed_scene', "[$num]");
		return $this->verify($result);
	
	}
	
	/*
		Установка таймера на выключение
	*/
	
	public function setDelayOff($seconds = 60) {
		
		if ( ($seconds < 0) or ($seconds > 21600) ) $seconds = 60;
		$result = $this->dev->msgSendRcv('delay_off', "[$seconds]");
		return $this->verify($result);
	
	}
	
	/*
		Проверка ответа
	*/
	
	private function verify ($result) {
		
		if ($result) {
			if ($this->dev->data != '') {
				$res = json_decode($this->dev->data);
				if (isset($res->{'result'})) {
					if ($res->{'result'}[0] == 'ok') return true;
				} else if (isset($res->{'error'})) {
					$this->error = $res->{'error'}->{'message'};
					return false;
				}
			} else {
				$this->error = 'Нет данных';
				return false;
			}
		} else {
			$this->error = 'Ответ не получен';
			return false;
		}
		
	}
}
