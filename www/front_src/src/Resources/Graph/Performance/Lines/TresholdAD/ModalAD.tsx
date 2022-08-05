import Modal from '@mui/material/Modal';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';

import TimePeriodButtonGroup from '../../TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../ExportableGraphWithTimeline';

interface Props {
  details: any;
  isOpen: boolean;
  setIsOpen: any;
}

const style = {
  bgcolor: 'background.paper',
  border: '2px solid #000',
  boxShadow: 24,
  left: '50%',
  p: 4,
  position: 'absolute' as const,
  top: '50%',
  transform: 'translate(-50%, -50%)',
  width: '80%',
};

const ModalAD = ({ isOpen, setIsOpen, details }: Props): JSX.Element => {
  const handleClose = (): void => setIsOpen(false);

  return (
    <Modal
      aria-describedby="modal-modal-description"
      aria-labelledby="modal-modal-title"
      open={isOpen}
      onClose={handleClose}
    >
      <Box sx={style}>
        <div>
          <TimePeriodButtonGroup />
        </div>
        <div>
          <ExportablePerformanceGraphWithTimeline
            graphHeight={200}
            resource={details}
          />
        </div>
        <div>footer</div>
        <div>
          <Button onClick={handleClose}>Close</Button>
        </div>
      </Box>
    </Modal>
  );
};

export default ModalAD;
