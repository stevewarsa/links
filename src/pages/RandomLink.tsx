import {useEffect, useState} from "react";
import {Link} from "../model/link";
import {useSelector} from "react-redux";
import {AppState} from "../model/AppState";
import linkService from "../services/LinkService";
import SpinnerTimer from "../components/SpinnerTimer";
import {Alert, Button, Col, Container, Form, ListGroup, Modal, Row} from "react-bootstrap";
import {Utils} from "../helpers/utils";

const RandomLink = () => {
    const links: Link[] = useSelector((st: AppState) => st.links);
    const [busy, setBusy] = useState({state: false, message: ""});
    const [domains, setDomains] = useState<string[]>([]);
    const [filteredDomains, setFilteredDomains] = useState<string[]>([]);
    const [randomDomains, setRandomDomains] = useState<string[]>([]);
    const [currIndex, setCurrIndex] = useState(0);
    const [showConfirm, setShowConfirm] = useState(false);
    const [showDeleteSuccess, setShowDeleteSuccess] = useState(false);
    const [showDeleteFail, setShowDeleteFail] = useState(false);
    const [indexToDelete, setIndexToDelete] = useState(-1);
    const [lastDeletedDomain, setLastDeletedDomain] = useState("N/A");
    const [searchText, setSearchText] = useState("");

    useEffect(() => {
        // go through all these links and just get a distinct list of domains
        if (!links || links.length === 0) {
            return;
        }
        (async () => {
            setBusy({state: true, message: "Loading domain exceptions from DB..."});
            const locDomainExceptionsData = await linkService.getDomainExceptions();
            const locDomainExc: string[] = locDomainExceptionsData.data;

            const apologeticsDomains: string[] = [];
            for (const link of links) {
                if (link.category !== "apologetics") {
                    continue;
                }
                let url = new URL(link.url);
                let domain = url.hostname;
                domain = domain.replace('www.','');
                if (!apologeticsDomains.find(d => d === domain) && !locDomainExc.find(d => d === domain)) {
                    // doesn't exist in apologetics domain yet, and is not an exception, so put it in the array
                    apologeticsDomains.push(domain);
                }
            }
            const sortedDomains = apologeticsDomains.sort();
            setDomains(sortedDomains);
            setFilteredDomains(sortedDomains);
            Utils.shuffleArray(apologeticsDomains)
            setRandomDomains(apologeticsDomains);
            setBusy({state: false, message: ""});
        })();
    }, [links]);

    const handleRandomLink = () => {
        window.open("http://" + randomDomains[currIndex], "_blank");
        setCurrIndex(prevIndex => {
            if ((prevIndex + 1) >= randomDomains.length) {
                return 0;
            } else {
                return prevIndex + 1;
            }
        });
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handleYes = () => {
        setShowConfirm(false);
        setBusy({state: true, message: "Deleting domain " + filteredDomains[indexToDelete] + "..."});
        setLastDeletedDomain(filteredDomains[indexToDelete]);
        linkService.deleteDomain(filteredDomains[indexToDelete]).then(resp => {
            if (resp.data === "TRUE") {
                // successful response
                filteredDomains.splice(indexToDelete, 1);
                domains.splice(indexToDelete, 1);
                setRandomDomains(prev => prev.filter(dm => dm !== filteredDomains[indexToDelete]));
                setShowDeleteSuccess(true);
            } else {
                setShowDeleteFail(true);
            }
            setBusy({state: false, message: ""});
        });
    };
    const handleNo = () => {
        setShowConfirm(false);
    };

    const handleDeleteDomain = (index: number) => {
        setShowConfirm(true);
        setIndexToDelete(index);
    };


    const handleSearchChange = (evt: any) => {
        const locSearchText = evt.target.value.trim();
        setSearchText(locSearchText);
        setFilteredDomains(domains.filter(dm => dm.toUpperCase().includes(locSearchText.toUpperCase())));
    };

    if (busy.state) {
        return <SpinnerTimer message={busy.message} />;
    } else {
        return (
            <Container>
                {showDeleteSuccess &&
                    <Alert variant="success" onClose={() => setShowDeleteSuccess(false)} dismissible>
                        <Alert.Heading>Domain Deleted</Alert.Heading>
                        <p>
                            The domain {lastDeletedDomain} has been successfully deleted!
                        </p>
                    </Alert>
                }
                {showDeleteFail &&
                    <Alert variant="danger" onClose={() => setShowDeleteFail(false)} dismissible>
                        <Alert.Heading>Domain NOT Deleted</Alert.Heading>
                        <p>
                            The domain {lastDeletedDomain} WAS NOT deleted! Check logs!
                        </p>
                    </Alert>
                }
                <Row className="text-center mb-3">
                    <Col>
                        <Button onClick={handleRandomLink} size="lg" variant="primary">Random Link</Button>
                    </Col>
                </Row>
                {filteredDomains &&
                    <Row className="text-center mb-3">
                        <Col>
                            <Form.Control
                                type="text"
                                id="searchText"
                                placeholder="Search Text"
                                value={searchText}
                                onChange={handleSearchChange}
                            />
                        </Col>
                    </Row>
                }
                <ListGroup>
                    {filteredDomains.map((dm, index) => (
                        <ListGroup.Item key={dm}>
                            {(index + 1) + ". "}<a href={"http://" + dm} target="_blank" rel="noreferrer">{"http://" + dm}</a>
                            <Button onClick={() => handleDeleteDomain(index)} className="ms-3" size="sm" variant="danger">X</Button>
                        </ListGroup.Item>
                    ))}
                </ListGroup>
                <Modal show={showConfirm} onHide={handleCancel}>
                    <Modal.Header closeButton>
                        <Modal.Title>Delete?</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>Are you sure you would like to delete domain {domains[indexToDelete]}?</Modal.Body>
                    <Modal.Footer>
                        <Button variant="primary" onClick={handleYes}>Yes</Button>
                        <Button variant="secondary" onClick={handleNo}>No</Button>
                    </Modal.Footer>
                </Modal>
            </Container>
        );
    }
};

export default RandomLink;