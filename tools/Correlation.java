package tools;

public class Correlation implements Comparable<Correlation> {
  private int count;
  private double r;

  public Correlation(double[] v1, double[] v2) {
    double sum_xy = 0, sum_x = 0, sum_y = 0, sum_sqx = 0, sum_sqy = 0;
    count = 0;
    r =0;
    if (v1 == null || v2 == null) {
      return;
    }
    int length = v1.length;
    if (length > v2.length) {
      length = v2.length;
    }
    for (int i =0; i <length; i++) {
      double x = v1[i];
      double y = v2[i];
      if (!Double.isNaN(x) && !Double.isNaN(y)) {
        count ++;
        sum_xy += x * y;
        sum_x += x;
        sum_y += y;
        sum_sqx += x * x;
        sum_sqy += y * y;
      }
    }
    if (count != 0) {
      r = (sum_xy - 1.0/count * sum_x * sum_y)/
        Math.sqrt(sum_sqx - 1.0/count * sum_x * sum_x)/
        Math.sqrt(sum_sqy - 1.0/count * sum_y * sum_y);
    }
    if (Double.isNaN(r)) {
      r = 0.0;
    }
  }

  public double coefficient() {
    return r;
  }

  public int count() {
    return count;
  }

  public int compareTo(Correlation another) {
    return Double.compare(r, another.r);
  }

  public String toString() {
    String s = "";
    s += String.format("%f\t%d", coefficient(), count());
    return s;
  }

}

