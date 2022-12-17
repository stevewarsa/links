import axios from "axios";

class LinkService {
    public getLinks() {
        return axios.get("/links-app/server/get_links.php");
    }
    public getCategories() {
        return axios.get("/links-app/server/get_categories.php");
    }
}

export default new LinkService();