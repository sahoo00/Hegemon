/**
 * The HelloWorldApp class implements an application that
 * simply prints "Hello World!" to standard output.
 */
import java.io.*;
import java.util.*;

class Hegemon {
  String pre;
  PrintStream out = System.out;

  public Hegemon(String p) {
    pre = p;
  }

  public boolean isExpr() {
    File f = new File(pre);
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public boolean hasBv() {
    File f = new File(getBv());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getBv() {
    return pre + "-bv.txt";
  }

  public boolean hasInfo() {
    File f = new File(getInfo());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getInfo() {
    return pre + "-info.txt";
  }

  public String getLine(String bvFile, String id) throws FileNotFoundException, IOException {
    String line;
    FileReader fileReader = 
      new FileReader(bvFile);

    // Always wrap FileReader in BufferedReader.
    BufferedReader bufferedReader = 
      new BufferedReader(fileReader);

    while((line = bufferedReader.readLine()) != null) {
      if(line.startsWith(id + "\t")) {
        return line;
      }
    }   

    // Always close files.
    bufferedReader.close();         
    return null;
  }

    /**
   *  Bitvector file format
   *    0 - low
   *    1 - intermediate
   *    2 - high
   *    Argument type == 0 : returns bitSet where char is 2
   *    Argument type == 1 : returns bitSet where char is not 1 and not blank
   */
  public static BitSet stringToBitSet(String str, int type) {
    BitSet res = new BitSet(str.length());
    //System.out.println(str.length());
    for (int i =0; i < str.length(); i++) {
      char c = str.charAt(i);
      res.clear(i);
      if (type == 0 && c == '2') {
        res.set(i);
      }
      if (type == 1 && !(c == '1' || c == ' ')) {
        res.set(i);
      }
    }
    //System.out.println(res.size());
    return res;
  }

  public boolean haveGoodDynamicRange(int num, BitSet va_thr) {
    int outside = va_thr.cardinality();
    if (num > (3 * outside)) {
      return false;
    }
    else {
      return true;
    }
  }

  public void getBnum(int[] res, 
      BitSet a, BitSet a_thr, BitSet b, BitSet b_thr) {
    res[0] = res[1] = res[2] = res[3] = 0;
    if (a.length() == 0 || b.length() == 0) {
      return;
    }
    BitSet thrBits = (BitSet) a_thr.clone();
    thrBits.and(b_thr);
    BitSet tmp = (BitSet) thrBits.clone();
    BitSet v1 = (BitSet) a.clone();
    v1.or(b);
    tmp.andNot(v1);
    int c0 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) b.clone();
    v1.andNot(a);
    tmp.and(v1);
    int c1 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) a.clone();
    v1.andNot(b);
    tmp.and(v1);
    int c2 = tmp.cardinality();
    tmp = (BitSet) thrBits.clone();
    v1 = (BitSet) a.clone();
    v1.and(b);
    tmp.and(v1);
    int c3 = tmp.cardinality();

    res[0] = c0;
    res[1] = c1;
    res[2] = c2;
    res[3] = c3;
  }

  public void getEstNum(double[] res, int[] bnum) {
    res[0] = res[1] = res[2] = res[3] = 0.0;
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    int total = c0 + c1 + c2 + c3;
    if (total <= 0) {
      return;
    }
    res[0] = (c0 + c1) * (c0 + c2)/total;
    res[1] = (c1 + c0) * (c1 + c3)/total;
    res[2] = (c2 + c0) * (c2 + c3)/total;
    res[3] = (c3 + c1) * (c3 + c2)/total;
  }

  public void getSnum(double[] res, int[] bnum, double[] estnum) {
    res[0] = res[1] = res[2] = res[3] = 1.0;
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    int total = c0 + c1 + c2 + c3;
    if (total <= 0) {
      return;
    }
    double e0 = estnum[0];
    double e1 = estnum[1];
    double e2 = estnum[2];
    double e3 = estnum[3];
    res[0] = (e0 - c0 + 1)/Math.sqrt(e0 + 1);
    res[1] = (e1 - c1 + 1)/Math.sqrt(e1 + 1);
    res[2] = (e2 - c2 + 1)/Math.sqrt(e2 + 1);
    res[3] = (e3 - c3 + 1)/Math.sqrt(e3 + 1);
  }

  public void getPnum(double[] res, int[] bnum) {
    int c0 = bnum[0];
    int c1 = bnum[1];
    int c2 = bnum[2];
    int c3 = bnum[3];
    res[0] = 0.5 * c0 / (c0 + c1 + 1) + 0.5 * c0/(c0 + c2 + 1);
    res[1] = 0.5 * c1 / (c1 + c0 + 1) + 0.5 * c1/(c1 + c3 + 1);
    res[2] = 0.5 * c2 / (c2 + c0 + 1) + 0.5 * c2/(c2 + c3 + 1);
    res[3] = 0.5 * c3 / (c3 + c1 + 1) + 0.5 * c3/(c3 + c2 + 1);
  }

  public static String strJoin(String sSep, int[] aArr) {
    StringBuilder sbStr = new StringBuilder();
    for (int i = 0, il = aArr.length; i < il; i++) {
      if (i > 0)
        sbStr.append(sSep);
      sbStr.append(aArr[i]);
    }
    return sbStr.toString();
  }

  public static String strJoin(String sSep, double[] aArr) {
    StringBuilder sbStr = new StringBuilder();
    for (int i = 0, il = aArr.length; i < il; i++) {
      if (i > 0)
        sbStr.append(sSep);
      sbStr.append(String.format("%1$.3g", aArr[i]));
    }
    return sbStr.toString();
  }

  public Set<String> getFilter() throws FileNotFoundException, Exception {
    if (!hasInfo()) {
      return null;
    }
    String infoFile = getInfo();
    HashSet<String> res = new HashSet<String>();
    String line;
    ArrayList<String> idlist = new ArrayList<String>();
    ArrayList<Double> drlist = new ArrayList<Double>();
    ArrayList<Double> sdlist = new ArrayList<Double>();
    FileReader fileReader = new FileReader(infoFile);
    BufferedReader bufferedReader = new BufferedReader(fileReader);
    line = bufferedReader.readLine();
    while((line = bufferedReader.readLine()) != null) {
      String[] result = line.split("\\t", -2);
      if (result.length < 9) {
        continue;
      }
      double dr = Double.parseDouble(result[7]) - Double.parseDouble(result[6]);
      double sd = Double.parseDouble(result[8]);
      idlist.add(result[0]);
      drlist.add(new Double(dr));
      sdlist.add(new Double(sd));
    }
    bufferedReader.close(); 
    double[] drdata = new double[drlist.size()];
    double[] sddata = new double[sdlist.size()];
    for (int x=0; x<idlist.size(); x++) {
        drdata[x] = drlist.get(x);
        sddata[x] = sdlist.get(x);
    }
    Arrays.sort(drdata);
    Arrays.sort(sddata);
    double drthr = fitStep(drdata, 0, drdata.length-1);
    double sdthr = fitStep(sddata, 0, sddata.length-1);
    //out.println(drthr);
    //out.println(sdthr);
    //double[] data = {1, 1, 1, 2, 3, 1, 4, 5, 4, 6, 4, 5};
    //double thr = fitStep(data, 0, data.length-1);
    //out.println(thr);
    for (int x=0; x<idlist.size(); x++) {
        if (drlist.get(x) > drthr && sdlist.get(x) > sdthr) {
            res.add(idlist.get(x));
        }
    }
    return res;
  }

  public void printBoolean(String id) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      Set<String> keys = getFilter();
      String line1 = getLine(bvFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      if (result1.length < 2) {
        return;
      }
      BitSet va = stringToBitSet(result1[2], 0);
      BitSet va_thr = stringToBitSet(result1[2], 1);
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(bvFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      int[] bnum = new int[4];
      double[] estnum = new double[4];
      double[] snum = new double[4];
      double[] pnum = new double[4];

      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        if (result.length < 2) {
          continue;
        }
        if (keys != null && !keys.contains(result[0])) {
          continue;
        }
        int numArr = result[2].length();
        BitSet vb = stringToBitSet(result[2], 0);
        BitSet vb_thr = stringToBitSet(result[2], 1);
        if (!haveGoodDynamicRange(numArr, vb_thr)) {
          continue;
        }
        getBnum(bnum, va, va_thr, vb, vb_thr);
        getEstNum(estnum, bnum);
        getSnum(snum, bnum, estnum);
        getPnum(pnum, bnum);
        out.println(result[0] + "\t" + strJoin("\t", bnum) + "\t" +
            strJoin("\t", snum) + "\t" + strJoin("\t", pnum));
      }   

      // Always close files.
      bufferedReader.close();         
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + bvFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public static double sum(double[] data, int start, int end) {
    double sum = 0;
    for (int i = start; i <= end; i++) {
      sum += data[i];
    }
    return sum;
  }

  public static double mean(double[] data, int start, int end) {
    double sum = sum(data, start, end);
    int count = end - start + 1;
    if (count <= 1) {
      return sum;
    }
    return sum/count;
  }

  public static double mse(double[] data, int start, int end) {
    double m = mean(data, start, end);
    double sum = 0;
    for (int i = start; i <= end; i++) {
      sum += (m - data[i]) * (m - data[i]);
    }
    return sum;
  }

  public static double fitStep(double[] data, int start, int end) {
    int count = end - start + 1;
    if (count == 1) {
      return data[start];
    }
    if (count < 0) {
      return 0;
    }
    double[] sseArray = new double[count];
    for (int i = 0; i < sseArray.length; i++) {
      sseArray[i] = 0.0;
    }
    double sum = sum(data, start, end);
    double mean = mean(data, start, end);
    double sstot = mse(data, start, end);
    double sum1 = 0.0;
    int count1 = 0;
    double m1 = 0.0;
    double sum2 = sum;
    int count2 = count;
    double m2 = mean;
    double sum1sq = 0.0;
    double sum2sq = sstot;
    double sse = sum1sq + sum2sq;
    int label = 0;

    for (int i = 0; i < sseArray.length; i++) {
      double entry = data[i + start];
      count1 ++;
      count2 --;
      if (count2 == 0) {
        sseArray[i] = sstot;
        continue;
      }
      double tmp = (mean - (entry + sum1)/count1);
      sum1sq = sum1sq + (entry-mean) * (entry-mean) - tmp * tmp * count1
        + (count1 - 1) * (mean - m1) * (mean - m1);
      tmp = (mean - (sum2 - entry)/count2);
      sum2sq = sum2sq - (entry-mean) * (entry-mean) - tmp * tmp * count2
        + (count2 + 1) * (mean - m2) * (mean - m2);
      sum1 += entry;
      sum2 -= entry;
      m1 = sum1/count1;
      m2 = sum2/count2;
      sse = sum1sq + sum2sq;
      sseArray[i] = sse;
    }

    double bestSse = Double.MAX_VALUE;
    int bestIndex = 0;
    for (int i = 0; i < count; i++) {
      if (sseArray[i] < bestSse) {
        bestSse = sseArray[i];
        bestIndex = i + start;
      }
    }
    m1 = mean(data, start, bestIndex);
    m2 = mean(data, bestIndex + 1, end);
    if (m1 < m2) {
      label = 1;
    }
    double thr = (m1 + m2)/2;
    return thr;
  }

  public static void getExprData(String[] arr, double[] data) {
    for (int i = 0, il = data.length; i < il; i++) {
      data[i] = Double.NaN;
    }
    for (int i = 2, il = arr.length; i < il && i < data.length; i++) {
      try {
        double v = Double.parseDouble(arr[i]);
        data[i] = v;
      }
      catch (Exception e) {
      }
    }
  }

  public static double getCorrelation(double[] v1, double[] v2) {
    double sum_xy = 0, sum_x = 0, sum_y = 0, sum_sqx = 0, sum_sqy = 0;
    int count = 0;
    double res =0;
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
      res = (sum_xy - 1.0/count * sum_x * sum_y)/
        Math.sqrt(sum_sqx - 1.0/count * sum_x * sum_x)/
        Math.sqrt(sum_sqy - 1.0/count * sum_y * sum_y);
    }
    if (Double.isNaN(res)) {
      res = 0.0;
    }
    return res;
  }

/*
  private static HashMap sortByValues(HashMap map) { 
    List list = new LinkedList(map.entrySet());
    // Defined Custom Comparator here
    Collections.sort(list, new Comparator() {
        public int compare(Object o1, Object o2) {
        return ((Comparable) ((Map.Entry) (o1)).getValue())
        .compareTo(((Map.Entry) (o2)).getValue());
        }
        });

    // Here I am copying the sorted list in HashMap
    // using LinkedHashMap to preserve the insertion order
    HashMap sortedHashMap = new LinkedHashMap();
    for (Iterator it = list.iterator(); it.hasNext();) {
      Map.Entry entry = (Map.Entry) it.next();
      sortedHashMap.put(entry.getKey(), entry.getValue());
    } 
    return sortedHashMap;
  }
*/
  public static <K, V extends Comparable<? super V>> Map<K, V> sortByValues(
      Map<K, V> tempMap) {
    TreeMap<K, V> map = new TreeMap<>(buildComparator(tempMap));
    map.putAll(tempMap);
    return map;
  }

  public static <K, V extends Comparable<? super V>> Comparator<? super K>
    buildComparator(final Map<K, V> tempMap) {
      return (o2, o1) -> tempMap.get(o1).compareTo(tempMap.get(o2));
    }

  public void printCorrelation(String id) {
    if (!isExpr()) {
      return;
    }
    String exprFile = pre;
    String line;
    try {
      String line1 = getLine(exprFile, id);
      if (line1 == null) {
        return;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      double[] data1 = new double[result1.length];
      getExprData(result1, data1); 
      
      // FileReader reads text files in the default encoding.
      FileReader fileReader = 
        new FileReader(exprFile);

      // Always wrap FileReader in BufferedReader.
      BufferedReader bufferedReader = 
        new BufferedReader(fileReader);

      double[] data2 = new double[result1.length];
      HashMap<String, Double> hmap1 = new HashMap<String, Double>();
      HashMap<String, String> hmap2 = new HashMap<String, String>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2); // -2 : Don't discard trailing nulls
        getExprData(result, data2); 
        double res = getCorrelation(data1, data2);
        hmap1.put(result[0], res);
        hmap2.put(result[0], result[1]);
      }
      // Always close files.
      bufferedReader.close();         
      Map<String, Double> map = sortByValues(hmap1); 
      Set set2 = map.entrySet();
      Iterator iterator2 = set2.iterator();
      while(iterator2.hasNext()) {
        Map.Entry me2 = (Map.Entry)iterator2.next();
        out.println(me2.getValue() + "\t" + me2.getKey() + "\t" +
            hmap2.get(me2.getKey())); 
      }
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + exprFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
  }

  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java Hegemon <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
    if (cmd.equals("boolean") && args.length < 3) {
      System.out.println("Usage: java Hegemon boolean pre id");
      System.exit(1);
    }
    if (cmd.equals("boolean")) {
      Hegemon h = new Hegemon(args[1]);
      h.printBoolean(args[2]);
    }
    if (cmd.equals("corr") && args.length < 3) {
      System.out.println("Usage: java Hegemon corr expr.txt id");
      System.exit(1);
    }
    if (cmd.equals("corr")) {
      Hegemon h = new Hegemon(args[1]);
      h.printCorrelation(args[2]);
    }
  }
}
