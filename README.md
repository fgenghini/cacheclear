Requirements
------------
- You need to have composer installed.


Installation
------------
- Access the app folder and run:
	- composer install
- That is it =)


Usage
-----
- Edit cacheclear.php file
- Add the required urls and your credentials.
- Add the page or pages that you want to clear the caches in the end of the file.
- You can you use:
	- $crawler->clearCaches(12); = Clear the caches for all the sites on page 12)
	- $crawler->clearCaches(12, 20); = Clear the caches for all the sites from page 12 to 20)
- Then, you can run the script using:
	- php cacheclear.php