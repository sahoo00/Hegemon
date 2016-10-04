package tools;

public class LinearRegression {
  private int n;
  private double intercept, slope;
  private double r2;
  private double svar, svar0, svar1;

  public LinearRegression(double[] x, double[] y) {
    n = 0;
    slope = intercept = r2 = svar = svar0 = svar1 = Double.NaN;
    if (x == null || y == null) {
      return;
    }
    int length = x.length;
    if (length > y.length) {
      length = y.length;
    }

    // first pass
    double sumx = 0.0, sumy = 0.0, sumx2 = 0.0;
    for (int i = 0; i < length; i++) {
      if (!Double.isNaN(x[i]) && !Double.isNaN(y[i])) {
        n ++;
        sumx  += x[i];
        sumx2 += x[i]*x[i];
        sumy  += y[i];
      }
    }
    if (n == 0) {
      return;
    }
    double xbar = sumx / n;
    double ybar = sumy / n;

    // second pass: compute summary statistics
    double xxbar = 0.0, yybar = 0.0, xybar = 0.0;
    for (int i = 0; i < length; i++) {
      if (!Double.isNaN(x[i]) && !Double.isNaN(y[i])) {
        xxbar += (x[i] - xbar) * (x[i] - xbar);
        yybar += (y[i] - ybar) * (y[i] - ybar);
        xybar += (x[i] - xbar) * (y[i] - ybar);
      }
    }
    slope  = xybar / xxbar;
    intercept = ybar - slope * xbar;

    // more statistical analysis
    double rss = 0.0;      // residual sum of squares
    double ssr = 0.0;      // regression sum of squares
    for (int i = 0; i < length; i++) {
      if (!Double.isNaN(x[i]) && !Double.isNaN(y[i])) {
        double fit = slope*x[i] + intercept;
        rss += (fit - y[i]) * (fit - y[i]);
        ssr += (fit - ybar) * (fit - ybar);
      }
    }

    int degreesOfFreedom = n-2;
    r2    = ssr / yybar;
    svar  = rss / degreesOfFreedom;
    svar1 = svar / xxbar;
    svar0 = svar/n + xbar*xbar*svar1;
  }

  public double intercept() {
    return intercept;
  }

  public double slope() {
    return slope;
  }

  public double R2() {
    return r2;
  }

  public double interceptStdErr() {
    return Math.sqrt(svar0);
  }

  public double slopeStdErr() {
    return Math.sqrt(svar1);
  }

  public double predict(double x) {
    return slope*x + intercept;
  }

  public String toString() {
    String s = "";
    s += String.format("%.4f\t%.4f", slope(), intercept());
    s += String.format("\t%.4f", R2());
    return s;
  }

}

