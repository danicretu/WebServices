package webservicecalculator;

import javax.xml.ws.Endpoint;


public class CalculatorService {
	
	public static void main(String[] args){
		Endpoint.publish("http://localhost:9999/ws/calc", new CalculatorImplementation());
	}

}
