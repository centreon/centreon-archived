const getErrorMessage = (error) => {
  if (typeof error === 'string') return error;

  const { name, type: errorType, value, message } = error;

  if (message) {
    return message;
  }

  switch (errorType) {
    case 'required':
      return `${name || 'This field'} is required`;
    case 'email':
      return 'Please enter a valid email address';
    case 'maxLength':
      if (Array.isArray(value)) {
        return `${name || 'Field'} must have at most ${error.maxLength} items`;
      }

      return `${name || 'Field'} must be at most ${
        error.maxLength
      } characters long`;

    case 'minLength':
      if (Array.isArray(value)) {
        return `${name || 'Field'} must have at least ${error.minLength} items`;
      }

      return `${name || 'Field'} must be at least ${
        error.minLength
      } characters long`;

    case 'invalidDate':
      return `${name || 'Field'} is not valid`;
    default:
      return `${name || 'Field'} is invalid. Reason: ${error.reason}`;
  }
};

export default getErrorMessage;
