package hw2_ShuaiShao;

import java.io.File;

import edu.uci.ics.crawler4j.crawler.CrawlConfig;
import edu.uci.ics.crawler4j.crawler.CrawlController;
import edu.uci.ics.crawler4j.fetcher.PageFetcher;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtConfig;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtServer;

public class Controller {

	public static void main(String[] args) throws Exception {
		// TODO Auto-generated method stub
		String crawlStorageFolder = "data/crawl";
		int numberOfCrawlers = 7;
		int maxPagesToFetch = 5000;
		int maxDepthOfCrawling = 5;
		boolean includeBinaryContentInCrawling = true;
		
		CrawlConfig config = new CrawlConfig();
		config.setCrawlStorageFolder(crawlStorageFolder);
		config.setMaxPagesToFetch(maxPagesToFetch);
		config.setMaxDepthOfCrawling(maxDepthOfCrawling);
		config.setIncludeBinaryContentInCrawling(includeBinaryContentInCrawling);
		
		File fetch_file =new File("fetch.csv");
	    if(fetch_file.exists())
	    {       
	        fetch_file.delete();
	    }
		
	    File visit_file =new File("visit.csv");
	    if(visit_file.exists())
	    {       
	        visit_file.delete();
	    }
	    
	    File urls_file =new File("urls.csv");
	    if(urls_file.exists())
	    {       
	        urls_file.delete();
	    }
	    
	    File pagerank_file =new File("pagerankdata.csv");
	    if(pagerank_file.exists())
	    {       
	        pagerank_file.delete();
	    }
	    
	    File dir = new File("data/files/");
		if(!deleteDir(dir)){
			System.out.println("Failed to delete files folder!!!!!");
		}
		else{
			System.out.println("Succeeded to delete files folder!");
		}
		
		/*
         * Instantiate the controller for this crawl.
         */
		
		PageFetcher pageFetcher = new PageFetcher(config);
		RobotstxtConfig robotstxtConfig = new RobotstxtConfig();
		RobotstxtServer robotstxtServer = new RobotstxtServer(robotstxtConfig, pageFetcher);
		CrawlController controller = new CrawlController(config, pageFetcher, robotstxtServer);
		
		/*
		 * For each crawl, you need to add some seed urls. These are the first
		 * URLs that are fetched and then the crawler starts following links 
		 * which are found in these pages
		 */
		controller.addSeed("http://cinema.usc.edu/");
		/*
		 * Start the crawl. This is a blocking operation, meaning that your code
		 * will reach the line after this only when crawling is finished.
		 */
		controller.start(MyCrawler.class, numberOfCrawlers);
	}
	
	private static boolean deleteDir(File dir) {
        if (dir.isDirectory()) {
            String[] children = dir.list();
            for (int i=0; i<children.length; i++) {
                boolean success = deleteDir(new File(dir, children[i]));
                if (!success) {
                    return false;
                }
            }
        }
        return dir.delete();
    }
}
