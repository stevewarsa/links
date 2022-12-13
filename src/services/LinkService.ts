import axios from "axios";

class LinkService {
    public getLinks() {
        return axios.get("/links-app/server/get_links.php");
    }
}

export default new LinkService();