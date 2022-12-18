// noinspection CheckTagEmptyBody
import {useDispatch, useSelector} from "react-redux";
import {useEffect, useState} from "react";
import {stateActions} from "../store";
import linkService from "../services/LinkService";
import SpinnerTimer from "../components/SpinnerTimer";
import {Card, Col, Container, Form, Pagination, Row} from "react-bootstrap";
import {Link} from "../model/link";
import {Category} from "../model/category";
import {AppState} from "../model/AppState";

const recsPerPage = 5;

const AllEntries = () => {
    const dispatch = useDispatch();
    const links: Link[] = useSelector((st: AppState) => st.links);
    const categories: Category[] = useSelector((st: AppState) => st.categories);
    const [busy, setBusy] = useState({state: false, message: ""});
    const [page, setPage] = useState(1);
    const [pageLen, setPageLen] = useState(1);
    const [currPageLinks, setCurrPageLinks] = useState<Link[]>([]);
    const [currPageStartIndex, setCurrPageStartIndex] = useState(1);
    const [filteredLinks, setFilteredLinks] = useState<Link[]>([]);
    const [selectedCat, setSelectedCat] = useState<string>("");
    const [sentVal, setSentVal] = useState("All");

    useEffect(() => {
        (async () => {
            setBusy({state: true, message: "Loading links from DB..."});
            const locLinksData: any = await linkService.getLinks();
            dispatch(stateActions.setLinks(locLinksData.data));
            const locCategoriesData: any = await linkService.getCategories();
            dispatch(stateActions.setCategories(locCategoriesData.data));
            const numPagesRounded = Math.round(locLinksData.data.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(locLinksData.data);
            setBusy({state: false, message: ""});
        })();
    }, [dispatch]);

    useEffect(() => {
        if (!filteredLinks) {
            return;
        }
        // purpose here is to set the 5 links for the current page (e.g. page=1 should show links 0-4, page=2 should show 5-9, page=3 show 10-14 etc)
        const linksOnPage: Link[] = [];
        const startLinkIndex = (page - 1) * recsPerPage;
        for (let i = startLinkIndex; i < (startLinkIndex + recsPerPage) && i < filteredLinks.length; i++) {
            linksOnPage.push(filteredLinks[i]);
        }
        // console.log("useEffect[page] - page: ", page);
        // console.log("useEffect[page] - linksOnPage: ", linksOnPage);
        // console.log("useEffect[page] - startLinkIndex: ", startLinkIndex);
        setCurrPageStartIndex(startLinkIndex);
        setCurrPageLinks(linksOnPage);
    }, [page, filteredLinks]);

    const handleFirstPage = () => {
        setPage(1);
    };

    const handleNextPage = () => {
        setPage(prev => prev + 1);
    };

    const handlePrevPage = () => {
        setPage(prev => prev - 1);
    };

    const handleLastPage = () => {
        setPage(pageLen - 1);
    };

    const doFilter = (selectedCat: string, sent: string) => {
        let locFilteredLinks = links.filter(link => selectedCat.length === 0 || link.category === selectedCat);
        if (selectedCat === "apologetics") {
            console.log("doFilter - category is apologetics, filtering to sent value " + sent);
            locFilteredLinks = locFilteredLinks.filter(link => sent === "All" || link.sent === sent);
        }
        const numPagesRounded = Math.round(locFilteredLinks.length / recsPerPage);
        setPageLen(numPagesRounded + 1);
        setPage(1);
        setFilteredLinks(locFilteredLinks);
    }

    const handleCatChange = (evt: any) => {
        const locSelectedCat: string = evt.target.value;
        console.log("handleCatChange - selectedCat=" + locSelectedCat);
        if (!locSelectedCat || locSelectedCat.length === 0) {
            const numPagesRounded = Math.round(links.length / recsPerPage);
            setPageLen(numPagesRounded + 1);
            setPage(1);
            setFilteredLinks(links);
            setSelectedCat("");
        } else {
            setSelectedCat(locSelectedCat);
            doFilter(locSelectedCat, sentVal);
        }
    };

    const handleSent = (evt: any, radioVal: string) => {
        console.log("handleSent - here's the radioVal: ", radioVal);
        console.log("handleSent - here's the event: ", evt);
        const checked: boolean = evt.target.checked;
        if (checked) {
            setSentVal(radioVal);
            console.log("handleSent - calling doFilter(" + selectedCat + ", " + radioVal + ")");
            doFilter(selectedCat, radioVal);
        }
    };

    if (busy.state) {
        return <SpinnerTimer message={busy.message} />;
    } else {
        if (currPageLinks && categories && categories.length > 0) {
            return (
                <Container className="mt-3">
                    {currPageLinks.length > 0 &&
                        <Row className="text-center mb-3">
                            <Col>{currPageStartIndex + 1}-{currPageStartIndex + currPageLinks.length} of {filteredLinks.length}</Col>
                        </Row>
                    }
                    <Row className="text-center mb-3">
                        <Col>
                            <Form.Select aria-label="Category Selection" size="sm" onChange={handleCatChange}>
                                <option value=""></option>
                                {categories.map(category => (
                                    <option key={category.categoryCd} value={category.categoryCd}>{category.categoryTx}</option>
                                ))}
                            </Form.Select>
                        </Col>
                        {selectedCat && "apologetics" === selectedCat &&
                        <Col>
                            <Form.Check checked={sentVal == "All"} onChange={(evt) => handleSent(evt, "All")} inline label="All" name="sent" type="radio" id="All" />
                            <Form.Check checked={sentVal == "Y"} onChange={(evt) => handleSent(evt, "Y")} inline label="Y" name="sent" type="radio" id="Y" />
                            <Form.Check checked={sentVal == "N"} onChange={(evt) => handleSent(evt, "N")} inline label="N" name="sent" type="radio" id="N" />
                        </Col>
                        }
                    </Row>
                    {currPageLinks.length > 0 &&
                        <Row className="text-center">
                            <Col>
                                <Pagination size="lg" className="justify-content-center">
                                    <Pagination.First onClick={handleFirstPage} />
                                    <Pagination.Prev onClick={handlePrevPage} />
                                    <Pagination.Item disabled>{page}</Pagination.Item>
                                    <Pagination.Next onClick={handleNextPage} />
                                    <Pagination.Last onClick={handleLastPage} />
                                </Pagination>
                            </Col>
                        </Row>
                    }
                    {currPageLinks.length === 0 && selectedCat === "apologetics" && sentVal === "N" && <h3>No links unsent for category Apologetics</h3>}
                    {currPageLinks.length === 0 && selectedCat !== "apologetics" && <h3>No links for category '{selectedCat}'</h3>}
                    {currPageLinks.length > 0 && currPageLinks.map(l => (
                        <Card key={l.date_time_link_saved + l.title} border="light">
                            <Card.Header>{categories.filter(cat => cat.categoryCd === l.category).map(cat => cat.categoryTx)}{l.category === "apologetics" ? " (Sent? " + l.sent + ")" : ""}</Card.Header>
                            <Card.Body>
                                <Card.Title>{l.title}</Card.Title>
                                <Card.Text>
                                    <a href={l.url} target="_blank">{l.url}</a><br/>
                                    Date/Time Accessed: {l.date_time_link_saved}<br/>
                                    Addl Comments: {l.addlcomments}
                                </Card.Text>
                            </Card.Body>
                        </Card>
                    ))}
                    {currPageLinks.length > 0 &&
                        <Row className="text-center">
                            <Col>
                                <Pagination size="lg" className="justify-content-center">
                                    <Pagination.First onClick={handleFirstPage} />
                                    <Pagination.Prev onClick={handlePrevPage} />
                                    <Pagination.Item disabled>{page}</Pagination.Item>
                                    <Pagination.Next onClick={handleNextPage} />
                                    <Pagination.Last onClick={handleLastPage} />
                                </Pagination>
                            </Col>
                        </Row>
                    }
                </Container>
            );
        } else {
            return <h3>No Links loaded...</h3>
        }
    }
};

export default AllEntries;