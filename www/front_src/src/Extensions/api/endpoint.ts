interface Parameters {
  action: string;
  id: string;
  type: string;
}

const baseEndpoint = './api/internal.php?object=centreon_module&';

const buildEndpoint = (parameters: Parameters): string => {
  return `${baseEndpoint}action=${parameters.action}&id=${parameters.id}&type=${parameters.type}`;
};

export default buildEndpoint;
