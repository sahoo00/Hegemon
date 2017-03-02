package tools;

import java.io.*;
import java.util.*;

class BooleanNet {
  String pre;
  PrintStream out = System.out;
  String[] headers;
  int start;
  int end;

  public BooleanNet(String p) {
    pre = p;
    start = 2;
    headers = getHeaders();
    end = headers.length - 1;
  }

  public boolean isExpr() {
    File f = new File(pre);
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public boolean hasExpr() {
    File f = new File(getExpr());
    if(f.exists() && !f.isDirectory()) { 
      return true;
    }
    return false;
  }

  public String getExpr() {
    return pre + "-expr.txt";
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

  public String getExprFile() {
    String exprFile;
    if (isExpr()) {
      return pre;
    }
    else {
      if (hasExpr()) {
        return getExpr();
      }
    }
    return null;
  }

  public String[] getHeaders() {
    try {
      String exprFile = getExprFile();
      if (exprFile == null) {
        return null;
      }
      FileReader fileReader = new FileReader(exprFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      String line = bufferedReader.readLine();
      String[] result = line.split("\\t", -2);
      bufferedReader.close(); 
      return result;
    }
    catch (Exception e) {
      return null;
    }
  }

  public String[] get2Lines(String bvFile, String id1, String id2) throws FileNotFoundException, IOException {
    String line;
    String[] res = new String[2];
    FileReader fileReader = 
      new FileReader(bvFile);

    // Always wrap FileReader in BufferedReader.
    BufferedReader bufferedReader = 
      new BufferedReader(fileReader);

    int count = 0;
    while((line = bufferedReader.readLine()) != null) {
      if(line.startsWith(id1 + "\t")) {
        res[0] = line;
        count++;
      }
      if(line.startsWith(id2 + "\t")) {
        res[1] = line;
        count++;
      }
      if (count == 2) {
        break;
      }
    }   

    // Always close files.
    bufferedReader.close();         
    return res;
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
      BitSet a, BitSet a_thr, BitSet b, BitSet b_thr, BitSet groups) {
    res[0] = res[1] = res[2] = res[3] = 0;
    if (a.length() == 0 || b.length() == 0) {
      return;
    }
    BitSet thrBits = (BitSet) a_thr.clone();
    thrBits.and(b_thr);
    if (groups != null) { thrBits.and(groups); }
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

  public static int getBooleanRelationType(double[] snum, double[] pnum,
      double sthr, double pthr) {
    int rel = 0;
    for (int i = 0; i < 4; i++) {
      if (snum[i] > sthr && pnum[i] < pthr) {
        if (rel == 0) { rel = i + 1; }
        if (rel == 2 && i == 2) {
          rel = 5;
        }
        if (rel == 1 && i == 3) {
          rel = 6;
        }
      }
    }
    return rel;
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
    double drthr = Hegemon.fitStep(drdata, 0, drdata.length-1);
    double sdthr = Hegemon.fitStep(sddata, 0, sddata.length-1);
    System.err.println("drthr:" + drthr);
    System.err.println("sdthr:" + sdthr);
    //double[] data = {1, 1, 1, 2, 3, 1, 4, 5, 4, 6, 4, 5};
    //double thr = fitStep(data, 0, data.length-1);
    //out.println(thr);
    for (int x=0; x<idlist.size(); x++) {
        if (drlist.get(x) > drthr && sdlist.get(x) > sdthr) {
            res.add(idlist.get(x));
        }
    }
    System.err.println("Size:" + res.size());
    return res;
  }

  public BitSet getGroups(String listFile)  {
    if (listFile == null) {
      return null;
    }
    try {
      String exprFile = getExprFile();
      if (exprFile == null) {
        return null;
      }
      String line;
      FileReader fileReader = new FileReader(listFile);
      BufferedReader bufferedReader = new BufferedReader(fileReader);
      HashSet<String> idlist = new HashSet<String>();
      while((line = bufferedReader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        idlist.add(result[0]);
      }
      BitSet res = new BitSet(headers.length - start);
      //System.out.println(str.length());
      for (int i =start; i < end; i++) {
        res.clear(i - start);
        if (idlist.contains(headers[i])) {
          res.set(i - start);
        }
      }
      return res;
    }
    catch (Exception e) {
      return null;
    }
  }

  public void printBoolean(String id, String listFile) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      Set<String> keys = getFilter();
      BitSet groups = getGroups(listFile);
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
        getBnum(bnum, va, va_thr, vb, vb_thr, groups);
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

  public void printBoolean(String id) {
    printBoolean(id, null);
  }

  public void printClosure(String id, String sthr, String pthr, 
      String type, String depth) {
    printClosure(id, sthr, pthr, type, depth, null);
  }

  public void printClosure(String id, String sthr, String pthr, 
      String type, String depth, String listFile) {
    if (!hasBv()) {
      return;
    }
     try {
      double st = Double.parseDouble(sthr);
      double pt = Double.parseDouble(pthr);
      int t = Integer.parseInt(type);
      int d = Integer.parseInt(depth);
      //Set<String> keys = getFilter();
      Set<String> keys = null;
      BitSet groups = getGroups(listFile);
      Set<String> list = getBooleanRelations(id, st, pt, groups, keys, t);
      if (list == null) {
        return;
      }
      System.err.println(id + " " + list.size());
      for (int i = 0; i < d; i++) {
        Set<String> tmplist = new HashSet<String>();
        for (String id1 : list) {
          Set<String> list1 = getBooleanRelations(id1, st, pt, groups, keys, t);
          if (list1 != null) {
            System.err.println(id1 + " " + list1.size());
            tmplist.addAll(list1);
          }
        }
        list = tmplist;
      }
      for (String id1: list) {
        System.out.println(id1);
      }
    }
    catch (Exception ex) {
      ex.printStackTrace();
    }
  }

  public Set<String> getBooleanRelations(String id, double sthr, double pthr,
      BitSet groups, Set<String> keys, int type) {
    if (!hasBv()) {
      return null;
    }
    String bvFile = getBv();
    String line;
    try {
      HashSet<String> res = new HashSet<String>();
      String line1 = getLine(bvFile, id);
      if (line1 == null) {
        return null;
      }
      String[] result1 = line1.split("\\t", -2); // -2 : Don't discard trailing nulls
      if (result1.length < 2) {
        return null;
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
        getBnum(bnum, va, va_thr, vb, vb_thr, groups);
        getEstNum(estnum, bnum);
        getSnum(snum, bnum, estnum);
        getPnum(pnum, bnum);
        int t = getBooleanRelationType(snum, pnum, sthr, pthr);
        if (type == t) {
          res.add(result[0]);
        }
      }   

      // Always close files.
      bufferedReader.close();         
      return res;
    }
    catch(FileNotFoundException ex) {
      out.println( "Unable to open file '" + bvFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
    return null;
  }

  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java BooleanNet <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
    if (cmd.equals("boolean") && args.length < 3) {
      System.out.println("Usage: java BooleanNet boolean pre id <listFile>");
      System.exit(1);
    }
    if (cmd.equals("boolean")) {
      BooleanNet h = new BooleanNet(args[1]);
      if (args.length < 4) {
        h.printBoolean(args[2]);
      }
      else {
        h.printBoolean(args[2], args[3]);
      }
    }
    if (cmd.equals("closure") && args.length < 7) {
      System.out.println("Usage: java BooleanNet closure pre id sthr pthr type depth <listFile>");
      System.exit(1);
    }
    if (cmd.equals("closure")) {
      BooleanNet h = new BooleanNet(args[1]);
      if (args.length < 8) {
        h.printClosure(args[2], args[3], args[4], args[5], args[6]);
      }
      else {
        h.printClosure(args[2], args[3], args[4], args[5], args[6], args[7]);
      }
    }
  }
}
