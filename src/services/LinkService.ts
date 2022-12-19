import axios from "axios";

class LinkService {
    public getLinks() {
        return axios.get("/links-app/server/get_links.php");
    }
    public getCategories() {
        return axios.get("/links-app/server/get_categories.php");
    }
    public getDomainExceptions() {
        return axios.get<string[]>("/links-app/server/get_domain_exceptions.php");
    }
    public deleteDomain(domain: string) {
        return axios.post<string>("/links-app/server/remove_link.php", domain);
    }
}

export default new LinkService();