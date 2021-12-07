import * as React from 'react';

import { connect } from 'react-redux';
import { useNavigate } from 'react-router';

import { useRequest, postData } from '@centreon/ui';

import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import BaseWizard from '../../components/forms/baseWizard';

const getPollersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

const links = [
  {
    active: true,
    number: 1,
    path: routeMap.serverConfigurationWizard,
    prevActive: true,
  },
  { active: true, number: 2, path: routeMap.pollerStep1, prevActive: true },
  { active: true, number: 3 },
  { active: false, number: 4 },
];

interface Props {
  pollerData: Record<string, unknown>;
  setWizard: (data) => void;
}

const PollerStepTwoRoute = ({ setWizard, pollerData }: Props): JSX.Element => {
  const [pollers, setPollers] = React.useState<Array<unknown>>([]);

  const { sendRequest: getPollersRequest } = useRequest<Array<unknown>>({
    request: postData,
  });
  const { sendRequest: postWizardFormRequest } = useRequest<{
    success: boolean;
  }>({
    request: postData,
  });

  const navigate = useNavigate();

  const getPollers = (): void => {
    getPollersRequest({ data: null, endpoint: getPollersEndpoint }).then(
      setPollers,
    );
  };

  React.useEffect(() => {
    getPollers();
  }, []);

  const handleSubmit = (data): void => {
    const dataToPost = { ...data, ...pollerData };
    dataToPost.server_type = 'poller';

    postWizardFormRequest({
      data: dataToPost,
      endpoint: wizardFormEndpoint,
    })
      .then(({ success }) => {
        setWizard({ submitStatus: success });
        if (pollerData.linked_remote_master) {
          navigate(routeMap.pollerStep3);
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  return (
    <BaseWizard>
      <ProgressBar links={links} />
      <Form
        initialValues={pollerData}
        pollers={pollers}
        onSubmit={handleSubmit}
      />
    </BaseWizard>
  );
};

const mapStateToProps = ({ pollerForm }): Pick<Props, 'pollerData'> => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

export default connect(mapStateToProps, mapDispatchToProps)(PollerStepTwoRoute);
