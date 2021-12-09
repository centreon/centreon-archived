import * as React from 'react';

import { connect } from 'react-redux';
import { useNavigate } from 'react-router';

import { useRequest, postData } from '@centreon/ui';

import Form from '../forms/poller/PollerFormStepTwo';
import routeMap from '../../route-maps/route-map';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import { WizardFormProps } from '../models';

const getPollersEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getRemotesList';
const wizardFormEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';
interface Props
  extends Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'> {
  pollerData: Record<string, unknown>;
  setWizard: (data) => void;
}

const FormPollerStepTwo = ({
  setWizard,
  pollerData,
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
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
          goToNextStep();
        } else {
          navigate(routeMap.pollerList);
        }
      })
      .catch(() => undefined);
  };

  return (
    <Form
      goToPreviousStep={goToPreviousStep}
      initialValues={pollerData}
      pollers={pollers}
      onSubmit={handleSubmit}
    />
  );
};

const mapStateToProps = ({ pollerForm }): Pick<Props, 'pollerData'> => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setWizard: setPollerWizard,
};

const PollwerStepTwo = connect(
  mapStateToProps,
  mapDispatchToProps,
)(FormPollerStepTwo);

export default (
  props: Pick<WizardFormProps, 'goToNextStep' | 'goToPreviousStep'>,
): JSX.Element => {
  return <PollwerStepTwo {...props} />;
};
