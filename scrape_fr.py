import requests
from bs4 import BeautifulSoup
import os
from datetime import datetime

PARSER = 'html.parser'
TAG_ = "tag/*/"
# The directory where the image will be saved
directory = 'c:/backup/pictures/memes/auto-download-freerepublic'
baseurl = "https://www.freerepublic.com/"
date_string = datetime.now().strftime("%m-%d-%Y")
response = requests.get(baseurl)
soup = BeautifulSoup(response.text, PARSER)
for i in range(500):
    print(i)
    if "The Briefing Room ^" in response.text and "pookie18" in response.text:
        # now go to the link specified
        a_tags = soup.find_all('a', href=lambda x: x and 'gopbriefingroom.com/index.php/topic' in x, text='The Briefing Room ^')
        link = None
        for tag in a_tags:
            print(tag['href'])
            link = tag['href']
            break
        response = requests.get(link)
        soup = BeautifulSoup(response.text, PARSER)
        img_tags = soup.find_all('img', {'class': 'bbc_img resized'})

        imagesGotten = []
        # Print the src attribute of the image tag
        for img in img_tags:
            print(img['src'])
            if img['src'] in imagesGotten:
                print("Already downloaded " + img['src'] + ", skipping it")
                continue
            try:
                response = requests.get(img['src'], stream=True)
                imagesGotten.append(img['src'])
            except requests.exceptions.SSLError:
                continue
            image_name = os.path.basename(img['src'])

            # Create the directory if it does not exist
            if not os.path.exists(directory):
                os.makedirs(directory)

            i = 1
            add_on_num = ""
            file_path = directory + "/" + image_name
            file_name, file_ext = os.path.splitext(file_path)
            file_name = file_name + "_" + date_string
            while os.path.exists(file_name + add_on_num + file_ext):
                print("File name '" + file_name + add_on_num + "' exists, changing it to:")
                add_on_num = "_" + str(i)
                print(file_name + add_on_num)
                i += 1
            open(file_name + add_on_num + file_ext, "wb").write(response.content)
        break
    else:
        try:
            print("The Briefing Room ^ not found in page " + baseurl + TAG_ + url)
        except NameError:
            print("The Briefing Room ^ not found in page " + baseurl)
        next_page_link = soup.find("a", string="Next Page")
        if next_page_link:
            url = next_page_link["href"]
            response = requests.get(baseurl + TAG_ + url)
            soup = BeautifulSoup(response.text, PARSER)
        else:
            break
