package hw2_ShuaiShao;

import java.io.BufferedOutputStream;
import java.io.DataOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.util.ArrayList;
import java.util.List;

import com.opencsv.CSVWriter;

import edu.uci.ics.crawler4j.crawler.Page;
import edu.uci.ics.crawler4j.crawler.WebCrawler;
import edu.uci.ics.crawler4j.parser.HtmlParseData;
import edu.uci.ics.crawler4j.url.WebURL;

public class MyCrawler extends WebCrawler {
	//private final static Pattern FILTERS = Pattern.compile(".*(\\.(css|js|gif|jpg" + "|png|mp3|mp3|zip|gz))$");
	//static Map<WebURL, Integer> fetch = new HashMap<WebURL, Integer>();
	/**
	* This method receives two parameters. The first parameter is the page
	* in which we have discovered this new url and the second parameter is
	* the new url. You should implement this function to specify whether
	* the given url should be crawled or not (based on your crawling logic). * In this example, we are instructing the crawler to ignore urls that
	* have css, js, git, ... extensions and to only accept urls that start * with "http://www.viterbi.usc.edu/". In this case, we didn't need the * referringPage parameter to make the decision.
	*/
	@Override
	public boolean shouldVisit(Page referringPage, WebURL url) {
		String href = url.getURL().toLowerCase();
		String c2;
		
		if (href.startsWith("http://cinema.usc.edu/") || href.startsWith("https://cinema.usc.edu/")){
			c2 = "OK";
		}
		else if(href.contains("usc.edu")){
			c2 = "USC";
		}
		else{
			c2 = "outUSC";
		}
		
		try  {  
			 FileWriter writer = new  FileWriter("urls.csv",  true);  
		     CSVWriter csvWriter = new CSVWriter(writer, ',');
	         String[] strs = {url.getURL(), c2};
	         csvWriter.writeNext(strs);
	         csvWriter.close();
	     } catch  (Exception e) {  
	         e.printStackTrace();  
	     } 
		
		
		return (href.startsWith("http://cinema.usc.edu/") || href.startsWith("https://cinema.usc.edu/"));
	}
	@Override
	protected void handlePageStatusCode(WebURL webUrl, int statusCode, String statusDescription) {
	    //System.out.println("URL:" + webUrl.getURL() + "   statusCode: " + statusCode + "     statusDescription: " + statusDescription);
		 try  {  
			 FileWriter writer = new  FileWriter("fetch.csv",  true);  
		     CSVWriter csvWriter = new CSVWriter(writer, ',');
	         String[] strs = {webUrl.getURL(), String.valueOf(statusCode)};
	         csvWriter.writeNext(strs);
	         csvWriter.close();
	     } catch  (Exception e) {  
	         e.printStackTrace();  
	     }  
	}
	
	/**
	* This function is called when a page is fetched and ready to be processed by your program.
	*/
	@Override
	public void visit(Page page) {
		/*
		String url = page.getWebURL().getURL(); System.out.println("URL: " + url);
		if (page.getParseData() instanceof HtmlParseData) {
			HtmlParseData htmlParseData = (HtmlParseData) page.getParseData(); 
			String text = htmlParseData.getText();
			String html = htmlParseData.getHtml();
			Set<WebURL> links = htmlParseData.getOutgoingUrls();
			System.out.println("Text length: " + text.length()); 
			System.out.println("Html length: " + html.length()); 
			System.out.println("Number of outgoing links: " + links.size());
		} */
		try {
			FileWriter writer = new  FileWriter("visit.csv",  true);  
			CSVWriter csvWriter = new CSVWriter(writer, ',');
			String[] strs = {page.getWebURL().getURL(), String.valueOf(page.getContentData().length), String.valueOf(page.getParseData().getOutgoingUrls().size()), page.getContentType().split(";")[0]};
			csvWriter.writeNext(strs);
			csvWriter.close();
		} catch  (Exception e) {  
	         e.printStackTrace();  
	    }
		
		if (page.getContentType().startsWith("text/html")||page.getContentType().startsWith("text/htm")||page.getContentType().startsWith("application/pdf")||page.getContentType().startsWith("application/doc")){
			String url = page.getWebURL().getURL(); 
			System.out.println("URL: " + url + "  content-type: "+page.getContentType() );
			try  {  
		         FileWriter pgr_writer = new  FileWriter("pagerankdata.csv",  true);  
			     CSVWriter pgr_csvWriter = new CSVWriter(pgr_writer, ',');
		         ArrayList<String> pgr_strs = new ArrayList<String>();
		         String end;
		         if (page.getContentType().startsWith("text/html")||page.getContentType().startsWith("text/htm")){
		        	 end = ".html";
		         }
		         else {
		        	 end = "";
		         }
		         pgr_strs.add(page.getWebURL().getURL().replace("/", "|")+end);
		         if (page.getParseData() instanceof HtmlParseData) {
		        	 HtmlParseData htmlParseData = (HtmlParseData) page.getParseData();
		        	 for (WebURL weburl: htmlParseData.getOutgoingUrls()){
		        		 pgr_strs.add(weburl.getURL().replace("/", "|")+end);
		        	 }
		         }
		         String[] pgr = (String[])pgr_strs.toArray(new String[0]);
		         pgr_csvWriter.writeNext(pgr);
		         pgr_csvWriter.close();
		         
		         File dir = new File("data/files/");
		         if(!dir.exists()) {    
		             dir.mkdirs();
		         }  
		         String file_name = "data/files/"+url.replace("/", "|")+end;
		         System.out.println(file_name);
		         File file = new File(file_name);
		         if (!file.exists()){
		        	 file.createNewFile();
		         }
		         
		         DataOutputStream out=new DataOutputStream(  
                         new BufferedOutputStream(  
                         new FileOutputStream(file))); 
		         out.write(page.getContentData());
		         out.close();
		     } catch  (Exception e) {  
		         e.printStackTrace();  
		     }
		}
		else {
			System.out.println("Not Valid Content Type: "+page.getContentType());
		}
	}
}
