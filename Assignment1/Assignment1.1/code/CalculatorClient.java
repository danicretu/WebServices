package webservicecalculator;

import java.net.URL;
import java.util.Scanner;

import javax.xml.namespace.QName;
import javax.xml.ws.Service;
import javax.xml.ws.WebServiceRef;

public class CalculatorClient {

	
	public static void main(String[] args) throws Exception{
		
		URL url = new URL("http://localhost:9999/ws/calc?wsdl");
		
		QName qname = new QName("http://webservicecalculator/", "CalculatorImplementationService");

        Service service = Service.create(url, qname);

        CalculatorInterface calc = service.getPort(CalculatorInterface.class);
        
        Scanner in = new Scanner(System.in);
	
        
        
        System.out.println("please enter operation ('add', 'sub', 'mul', 'div') or 'exit' to exit");
        
        String operation = in.nextLine();
        
        while (!operation.equals("exit")){
        	
        	System.out.println("please enter first number: ");
        	
        	double a = in.nextDouble();
        	
        	System.out.println("please enter second number: ");
        	
        	double b = in.nextDouble();
        	
        	if (operation.equals("add")){
        		System.out.println(calc.add(a, b));
        	} else if (operation.equals("mul")){
        		System.out.println(calc.mul(a, b));
        	} else if (operation.equals("sub")){
        		System.out.println(calc.sub(a, b));
        	} else if (operation.equals("div")){
        		System.out.println(calc.div(a, b));
        	} else if (operation.equals("exit")){
        		System.exit(0);
        	} else {
        		System.out.println("Please enter valid operation or exit");
        	}
        	
        	System.out.println("please enter operation ('add', 'sub', 'mul', 'div') or 'exit' to exit");
        	
        	operation = in.next();
        }
        
        
        
	
	} 
	

}
