package tools;

import java.io.*;
import java.util.*;
import java.util.stream.*;

class Embedding extends Hegemon {
  int blocksize_ = 8000;
  int[][] be;
  int axis = 0;
  int count1 = 0;
  int count2 = 0;
  int x0_ = 1;
  int x1_ = 400;
  int x2_ = 400;
  int x3_ = 1;

  public Embedding(String p) {
    super(p);
    be = new int[2][];
    be[0] = new int[end - start + 1];
    be[1] = new int[end - start + 1];
    for (int i = 0; i < (end - start + 1); i++) {
      be[0][i] = 0;
      be[1][i] = 0;
    }
  }

  public static int getInt(String s) {
    try {
      double v = Integer.parseInt(s);
      return v;
    }
    catch (Exception e) {
    }
    return 0;
  }

  public void setParams(String x0, String x1, String x2, String x3) {
    x0_ = getInt(x0);
    x1_ = getInt(x1);
    x2_ = getInt(x2);
    x3_ = getInt(x3);
  }

  public static ArrayList<String> getIDHash(String exprFile, HashMap<String, Long> idhash) {
    try {
      HashMap<String, Long> res = idhash;
      ArrayList<String> idlist = new ArrayList<String>();
      FileR reader = new FileR(exprFile);
      String line = reader.readLine();
      long pos = reader.filePtr();
      while((line = reader.readLine()) != null) {
        String[] result = line.split("\\t", -2);
        if (result.length < 1) {
          continue;
        }
        res.put(result[0], pos);
	idlist.add(result[0]);
        //System.out.println(result[0] + " " + pos);
        pos = reader.filePtr();
      }
      reader.close();
      return idlist;
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
    return null;
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

  public static BitSet getQuadrant(
      BitSet a, BitSet a_thr, BitSet b, BitSet b_thr, int q) {
    if (a.length() == 0 || b.length() == 0) {
      return null;
    }
    BitSet thrBits = (BitSet) a_thr.clone();
    thrBits.and(b_thr);
    BitSet tmp = (BitSet) thrBits.clone();
    if (q == 0) {
      BitSet v1 = (BitSet) a.clone();
      v1.or(b);
      tmp.andNot(v1);
      return tmp;
    }
    if (q == 1) {
      BitSet v1 = (BitSet) b.clone();
      v1.andNot(a);
      tmp.and(v1);
      return tmp;
    }
    if (q == 2) {
      BitSet v1 = (BitSet) a.clone();
      v1.andNot(b);
      tmp.and(v1);
      return tmp;
    }
    if (q == 3) {
      BitSet v1 = (BitSet) a.clone();
      v1.and(b);
      tmp.and(v1);
      return tmp;
    }

    return null;
  }

  public void updateBE(
      BitSet a, BitSet a_thr, BitSet b, BitSet b_thr, int t,
      int c0, int c1, int c2, int c3) {
    if (t == 2 || t == 3 || t == 5) {
      count1 ++;
      BitSet q0 = getQuadrant(a, a_thr, b, b_thr, 0);
      BitSet q3 = getQuadrant(a, a_thr, b, b_thr, 3);
      for (int i = 0; i < (end - start + 1); i++) {
        if (q0.get(i)) {
          be[1][i] += c0;
        }
        if (q3.get(i)) {
          be[0][i] += c3;
        }
      }
    }
    if (t == 1 || t == 4 || t == 6) {
      count2 ++;
      BitSet q1 = getQuadrant(a, a_thr, b, b_thr, 1);
      BitSet q2 = getQuadrant(a, a_thr, b, b_thr, 2);
      for (int i = 0; i < (end - start + 1); i++) {
        if (q1.get(i)) {
          be[0][i] += c1;
        }
        if (q2.get(i)) {
          be[1][i] += c2;
        }
      }
    }
    axis = 1 - axis;
  }

  public void printBE() {
    for (int i = 0; i < (end - start + 1); i++) {
      out.println(be[0][i] + "\t" + be[1][i]);
    }
    System.err.println(count1 + "\t" + count2);
  }

  public boolean manyHi(BitSet a, double nhithr) {
    int numhi = a.cardinality();
    if (numhi > nhithr * (end - start + 1)) {
      return true;
    }
    return false;
  }

  public void printEmbedding(String sThr_t, String pThr_t, String nhiThr_t) {
    if (!hasBv()) {
      return;
    }
    String bvFile = getBv();
    String line;
    try {
      double sthr = getDouble(sThr_t);
      double pthr = getDouble(pThr_t);
      double nhithr = getDouble(nhiThr_t);
      HashMap<String, Long> idhash = new HashMap<String, Long>();
      ArrayList<String> idlist = getIDHash(bvFile, idhash);
      FileR reader = new FileR(bvFile);
      int[] bnum = new int[4];
      double[] estnum = new double[4];
      double[] snum = new double[4];
      double[] pnum = new double[4];
      int numArr = 0;
      for (int b1 = 0;  b1 < idlist.size(); b1+=blocksize_) {
	BitSet[] ba1 = new BitSet[blocksize_];
	BitSet[] ba1_thr = new BitSet[blocksize_];
	Long ptr = idhash.get(idlist.get(b1));
	reader.seek(ptr);
	for (int i = b1; i < (b1+blocksize_) && i < idlist.size(); i++) {
	  String ga = reader.readLine();
	  if (ga == null) {
	    break;
	  }
	  String[] result1 = ga.split("\\t", -2); // -2 : Don't discard trailing nulls
	  if (result1.length < 2) {
	    continue;
	  }
	  numArr = result1[2].length();
	  BitSet va = stringToBitSet(result1[2], 0);
	  BitSet va_thr = stringToBitSet(result1[2], 1);
	  ba1[i-b1] = va;
	  ba1_thr[i-b1] = va_thr;
	}
	for (int b2 = b1;  b2 < idlist.size(); b2+=blocksize_) {
	  System.err.println("Block = (" + b1 + ", " + b2 + ")");
	  BitSet[] ba2 = new BitSet[blocksize_];
	  BitSet[] ba2_thr = new BitSet[blocksize_];
	  ptr = idhash.get(idlist.get(b2));
	  reader.seek(ptr);
	  for (int i = b2; i < (b2+blocksize_); i++) {
	    String ga = reader.readLine();
	    if (ga == null) {
	      break;
	    }
	    String[] result1 = ga.split("\\t", -2); // -2 : Don't discard trailing nulls
	    if (result1.length < 2) {
	      continue;
	    }
	    BitSet vb = stringToBitSet(result1[2], 0);
	    BitSet vb_thr = stringToBitSet(result1[2], 1);
	    ba2[i-b2] = vb;
	    ba2_thr[i-b2] = vb_thr;
	  }
	  BitSet va = ba1[0];
	  for (int i = b1; va != null && i < (b1+blocksize_); i++) {
	    System.err.println(i);
	    va = ba1[i-b1];
	    BitSet va_thr = ba1_thr[i-b1];
	    BitSet vb = ba2[0];
	    if (!haveGoodDynamicRange(numArr, va_thr)) {
	      continue;
	    }
	    if (!manyHi(va, nhithr)) {
	      continue;
	    }
	    for (int j = b2; vb != null && j < (b2 + blocksize_); j++) {
	      vb = ba2[j-b2];
	      BitSet vb_thr = ba2_thr[j-b2];
	      if (!haveGoodDynamicRange(numArr, vb_thr)) {
		continue;
	      }
              if (!manyHi(vb, nhithr)) {
                continue;
              }
	      getBnum(bnum, va, va_thr, vb, vb_thr, null);
	      getEstNum(estnum, bnum);
	      getSnum(snum, bnum, estnum);
	      getPnum(pnum, bnum);
              int t = getBooleanRelationType(snum, pnum, sthr, pthr);
              updateBE(va, va_thr, vb, vb_thr, t, x0_, x1_, x2_, x3_);
	      if (j < (b2 -1 + blocksize_)) {
		vb = ba2[j+1-b2];
	      }
	    }
	    if (i < (b1-1 +blocksize_)) {
	      va = ba1[i+1-b1];
	    }
	  } // end ga
	} // end gb2
      } // end gb1
    }
    catch(FileNotFoundException ex) {
      System.err.println( "Unable to open file '" + bvFile + "'");
    }
    catch(Exception ex) {
      ex.printStackTrace();
    }
    printBE();
  }

  public static void main(String[] args) {
    if (args.length < 1) {
      System.out.println("Usage: java Hegemon <cmd> <args> ... <args>");
      System.exit(1);
    }
    String cmd = args[0];
    if (cmd.equals("boolean") && args.length < 9) {
      System.out.println("Usage: java Embedding boolean pre sThr pThr nhiThr x0 x1 x2 x3");
      System.exit(1);
    }
    if (cmd.equals("boolean")) {
      Embedding h = new Embedding(args[1]);
      if (args.length >= 9) {
        h.printEmbedding(args[2], args[3], args[4]);
        h.setParams(args[5], args[6], args[7], args[8]);
      }
    }
  }
}
