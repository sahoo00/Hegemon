package tools;

import java.awt.*;
import java.awt.geom.AffineTransform;
import java.awt.image.BufferedImage;
import java.io.*;
import java.util.Iterator;

import javax.imageio.*;
import javax.imageio.stream.FileImageOutputStream;

public class Heatmap {

    String sFile;

    public Heatmap(String s) {
        sFile = s;
    }

    public void setSize(int anum, int gnum) {
    }

    public void init() { }
    public void close() { }
    public void plotCell(int x, int y, double val) {
       System.out.println("X = " + x + " Y = " + y + " Val = " + val);
    }
}

