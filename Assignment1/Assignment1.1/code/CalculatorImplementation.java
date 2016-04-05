package webservicecalculator;
import javax.jws.WebService;

@WebService(endpointInterface = "webservicecalculator.CalculatorInterface")
public class CalculatorImplementation implements CalculatorInterface{

	@Override
	public double add(double a, double b) {
		// TODO Auto-generated method stub
		return a + b;
	}

	@Override
	public double sub(double a, double b) {
		// TODO Auto-generated method stub
		return a-b;
	}

	@Override
	public double mul(double a, double b) {
		// TODO Auto-generated method stub
		return a*b;
	}

	@Override
	public double div(double a, double b) {
		// TODO Auto-generated method stub
		return a / b;
	}

}
