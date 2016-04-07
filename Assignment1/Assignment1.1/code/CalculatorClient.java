package webservicecalculator;

import java.net.URL;
import java.util.Scanner;

import javax.xml.namespace.QName;
import javax.xml.ws.Service;
import javax.xml.ws.WebServiceRef;

public class CalculatorClient {

	
	public static void main(String[] args) throws Exception{
		
		double a = 0;
		double b = 0;
		
		URL url = new URL("http://localhost:8080/ws/calc?wsdl");
		
		QName qname = new QName("http://webservicecalculator/", "CalculatorImplementationService");

        Service service = Service.create(url, qname);

        CalculatorInterface calc = service.getPort(CalculatorInterface.class);
        
        Scanner in = new Scanner(System.in);
	
        
        
       
        String operation = "";
        
        while (!operation.equals("exit")){
        	
        	System.out.println("please enter operation ('add', 'sub', 'mul', 'div') or 'exit' to exit");
        	
        	operation = in.next();
        	
        	if (operation.equals("exit")){
        		System.exit(0);
        	}
        	
        	System.out.println("please enter first number: ");
        	
        	try{
        		
        		a = in.nextDouble();
        		
        	} catch (Exception e){
        		System.out.println("please enter a number");
        		
        		continue;
        	}
        	
        	try{
        		System.out.println("please enter second number: ");
        		
        		b = in.nextDouble();
        		
        	} catch (Exception e){
        		System.out.println("please enter a number");
        		
        		continue;
        	}
        	
        	
        	
        	
        	if (operation.equals("add")){
        		System.out.println(calc.add(a, b));
        	} else if (operation.equals("mul")){
        		System.out.println(calc.mul(a, b));
        	} else if (operation.equals("sub")){
        		System.out.println(calc.sub(a, b));
        	} else if (operation.equals("div")){
        		if (b != 0){
        			System.out.println(calc.div(a, b));
        		} else {
        			System.out.println("cannot divide by 0");
        			System.out.println("please enter operation ('add', 'sub', 'mul', 'div') or 'exit' to exit");
                	
                	operation = in.next();
        			continue;
        		}
        		
        	} else if (operation.equals("exit")){
        		System.exit(0);
        	} else {
        		System.out.println("Please enter valid operation or exit");
        	}

        }
        
        
        
	
	} 
	

}
