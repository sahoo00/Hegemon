package tools;

import java.io.*;
import java.util.*;
import java.nio.channels.Channels;
import java.nio.charset.Charset;

public class FileR {
  
    String filename;
    RandomAccessFile raf;
    MyBR reader;
    InputStream is;
    InputStreamReader isr;
    long index = 0;
    final int size = 10000;
    
    public FileR (String f) {
      try {
        filename = f;
        raf = new RandomAccessFile(filename, "r");
        is = Channels.newInputStream(raf.getChannel());
        isr = new InputStreamReader(is, Charset.forName("UTF-8"));
        reader = new MyBR(isr, size);
      } catch (Exception ex) {
        ex.printStackTrace();
      }
    }

    public void close(){
      try {
        reader.close();
        raf.close();
      } catch (Exception ex) {
        ex.printStackTrace();
      }
    }

    public long getLineNo() {
        return index;
    }
    
    public synchronized long filePtr() throws IOException {
      return raf.getChannel().position() - size;
    }

    public String readLine() throws Exception {
      index++;
      return reader.readLine();
    }

    public void seek(long pos) throws IOException {
      if(pos < 0) { return; }
      if(pos > raf.length()) { return; }
      raf.seek(pos);
      reader = new MyBR(isr, size);
    }

}
