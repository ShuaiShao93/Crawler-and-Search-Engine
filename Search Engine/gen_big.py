import os, HTMLParser, sys

reload(sys)  
sys.setdefaultencoding('utf8')

root = "/Users/shuaishao/Documents/572/HW3/files"
outf = open("dic.txt", "w")
flag = True

class MyHTMLParser(HTMLParser.HTMLParser): 
	def __init__(self):
		HTMLParser.HTMLParser.__init__(self)
		self.flag = True

	def handle_starttag(self, tag, attrs):
		if tag == "head" or tag == "script" or tag == "style":
			self.flag = False

	def handle_data(self, data):  
		if data.strip() and self.flag:  
			outf.write(data.strip()+"\n")
		self.flag = True

parser = MyHTMLParser()
for rt, dirs, files in os.walk(root):
	for f in files:
		if f[0] != '.' and f[-4:] != ".pdf":
			print f
			file = open(root+'/'+f, "r")
			parser.feed(file.read())

outf.close()

 


